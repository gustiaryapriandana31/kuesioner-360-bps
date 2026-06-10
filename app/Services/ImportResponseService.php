<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Response;
use App\Models\ResponseJawaban;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ImportResponseService
{
    private const ASPEK_MAP = [
        'BERORIENTASI' => 1,
        'AKUNTABEL'    => 2,
        'KOMPETEN'     => 3,
        'HARMONIS'     => 4,
        'LOYAL'        => 5,
        'ADAPTIF'      => 6,
        'KOLABORATIF'  => 7,
        'Budaya'       => 8,
    ];

    /**
     * @return array{success: bool, message: string, stats?: array}
     */
    public function import(string $absolutePath, int $kuesionerId, string $status, int $userId, bool $deleteExisting = false): array
    {
        // Tingkatkan batas waktu eksekusi script menjadi 5 menit (300 detik)
        // karena proses baca Excel dan pencarian fuzzy text bisa memakan waktu lama.
        set_time_limit(300);

        $logger = Log::channel('import_response');
        $logger->info("Import dimulai (Sinkronous)", [
            'kuesioner_id' => $kuesionerId,
            'status'       => $status,
            'file'         => $absolutePath,
        ]);

        try {
            $spreadsheet  = IOFactory::load($absolutePath);
            $sheet        = $spreadsheet->getActiveSheet();
            // toArray($nullValue = null, $calculateFormulas = false, $formatData = true, $returnCellRef = false)
            // MatikancalculateFormulas agar error "Formula Error" karena string seperti = atau + tidak terjadi
            $rows         = $sheet->toArray(null, false, true, false);
        } catch (Throwable $e) {
            $logger->error("Gagal membaca file Excel", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => "Gagal membaca file Excel: {$e->getMessage()}"];
        }

        if (empty($rows)) {
            return ['success' => false, 'message' => "File kosong atau tidak dapat dibaca."];
        }

        $headerRow = array_shift($rows);
        $dataRows  = $rows;

        if (empty($dataRows)) {
            return ['success' => false, 'message' => "Tidak ada baris data setelah header."];
        }

        $kuesioner = \App\Models\Kuesioner::with('pertanyaans')->find($kuesionerId);
        if (! $kuesioner) {
            return ['success' => false, 'message' => "Kuesioner tidak ditemukan."];
        }

        // Map dari urutan ke ID database riil
        $urutanToIdMap = $kuesioner->pertanyaans->pluck('id', 'urutan')->all();

        $kolomMap = $this->parseHeader($headerRow, $urutanToIdMap, $logger);

        if (empty($kolomMap)) {
            return ['success' => false, 'message' => "Tidak ada kolom aspek yang valid ditemukan di header."];
        }

        $cachePegawai = [];
        $targetNamaUnik = collect($kolomMap)->pluck('nama_target')->unique();
        $targetMap      = [];

        foreach ($targetNamaUnik as $namaTarget) {
            $pegawai = $this->cariPegawai($namaTarget, $cachePegawai);
            if ($pegawai) {
                $targetMap[$namaTarget] = $pegawai->id;
            } else {
                $logger->warning("Target tidak ditemukan di DB", ['nama' => $namaTarget]);
            }
        }

        $involvedPegawaiIds = [];
        foreach ($targetMap as $namaTarget => $targetId) {
            $involvedPegawaiIds[$targetId] = true;
        }

        $stats = [
            'total_penilai'           => 0,
            'total_response'          => 0,
            'total_jawaban'           => 0,
            'penilai_tidak_ditemukan' => [],
            'target_tidak_ditemukan'  => array_values($targetNamaUnik->diff(array_keys($targetMap))->all()),
            'nilai_invalid'           => 0,
        ];

        // Mulai transaksi DB raksasa
        DB::beginTransaction();

        try {
            if ($deleteExisting) {
                $logger->info("Menghapus data respon lama untuk kuesioner ID: {$kuesionerId}");
                Response::where('kuesioner_id', $kuesionerId)->delete();
            }
            foreach ($dataRows as $rowIndex => $row) {
                $timestampRaw = $row[0] ?? null;
                $namaPenilai  = trim((string) ($row[1] ?? ''));

                if (empty($namaPenilai)) continue;

                $pegawaiPenilai = $this->cariPegawai($namaPenilai, $cachePegawai);
                if (! $pegawaiPenilai) {
                    $stats['penilai_tidak_ditemukan'][] = $namaPenilai;
                    continue;
                }

                if (! $pegawaiPenilai->user_id) continue;

                $involvedPegawaiIds[$pegawaiPenilai->id] = true;

                $timestamp = $this->parseTimestamp($timestampRaw);
                $stats['total_penilai']++;
                $responseIds = []; // Cache ID Response untuk insert jawaban nanti

                // 1. Insert Response
                foreach ($targetMap as $namaTarget => $targetId) {
                    $resp = Response::firstOrCreate(
                        [
                            'kuesioner_id' => $kuesionerId,
                            'penilai_id'   => $pegawaiPenilai->user_id,
                            'target_id'    => $targetId,
                        ],
                        [
                            'status'       => $status,
                            'submitted_at' => $timestamp,
                            'created_at'   => $timestamp,
                            'updated_at'   => now(),
                        ]
                    );

                    if ($resp->wasRecentlyCreated) {
                        $stats['total_response']++;
                    }
                    $responseIds[$targetId] = $resp->id;
                }

                // 2. Insert Response Jawaban
                foreach ($kolomMap as $colIndex => $meta) {
                    $nilaiRaw = $row[$colIndex] ?? null;
                    $targetId = $targetMap[$meta['nama_target']] ?? null;

                    if (is_null($nilaiRaw) || ! is_numeric($nilaiRaw) || ! $targetId) {
                        continue;
                    }

                    $nilai = (int) $nilaiRaw;

                    if ($nilai < 1 || $nilai > 10) {
                        $stats['nilai_invalid']++;
                        continue;
                    }

                    $responseId = $responseIds[$targetId] ?? null;
                    if (! $responseId) continue;

                    ResponseJawaban::updateOrCreate(
                        [
                            'response_id'   => $responseId,
                            'pertanyaan_id' => $meta['pertanyaan_id'],
                        ],
                        ['nilai' => $nilai]
                    );

                    $stats['total_jawaban']++;
                }
            }

            if ($kuesioner->status === 'closed') {
                $allActivePegawaiIds = Pegawai::query()
                    ->active()
                    ->assessable()
                    ->pluck('id')
                    ->all();

                $excludedPegawaiIds = [];
                foreach ($allActivePegawaiIds as $id) {
                    if (! isset($involvedPegawaiIds[$id])) {
                        $excludedPegawaiIds[] = $id;
                    }
                }

                $kuesioner->excluded_pegawai_ids = $excludedPegawaiIds;
                $kuesioner->save();

                // Clear caches so the changes are immediately visible
                \Illuminate\Support\Facades\Cache::forget('kuesioners:list:active_closed');
                \Illuminate\Support\Facades\Cache::forget("kuesioner:kode:{$kuesioner->kode}:with_pertanyaans");
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $logger->error("Gagal insert data ke DB", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => "Terjadi kesalahan database: {$e->getMessage()}"];
        }

        $stats['penilai_tidak_ditemukan'] = array_values(array_unique($stats['penilai_tidak_ditemukan']));
        $logger->info("Import selesai", $stats);

        return [
            'success' => true,
            'message' => "Import berhasil!",
            'stats'   => $stats,
        ];
    }

    private function parseHeader(array $headerRow, array $urutanToIdMap, mixed $logger): array
    {
        $kolomMap = [];
        for ($i = 2; $i < count($headerRow); $i++) {
            $headerText = (string) ($headerRow[$i] ?? '');
            if (empty(trim($headerText))) continue;

            $urutan = null;
            foreach (self::ASPEK_MAP as $keyword => $u) {
                if (str_contains($headerText, $keyword)) {
                    $urutan = $u;
                    break;
                }
            }
            if (is_null($urutan)) continue;

            $pertanyaanId = $urutanToIdMap[$urutan] ?? null;
            if (is_null($pertanyaanId)) {
                $logger->warning("Pertanyaan dengan urutan {$urutan} tidak ditemukan di kuesioner ini.");
                continue;
            }

            if (! preg_match('/\[(.+?)\]\s*$/', $headerText, $matches)) continue;

            $namaRaw    = $matches[1];
            $namaBersih = trim(preg_replace('/^\d+\.\s*/', '', $namaRaw));

            $kolomMap[$i] = [
                'nama_target'   => $namaBersih,
                'pertanyaan_id' => $pertanyaanId,
            ];
        }
        return $kolomMap;
    }

    private function cariPegawai(string $namaRaw, array &$cache): ?Pegawai
    {
        $namaBersih = trim(preg_replace('/\s+/', ' ', $namaRaw));
        if (array_key_exists($namaBersih, $cache)) {
            return $cache[$namaBersih];
        }
        
        $pegawai = Pegawai::cariByNama($namaBersih);
        return $cache[$namaBersih] = $pegawai;
    }

    private function parseTimestamp(mixed $raw): Carbon
    {
        if ($raw instanceof \DateTimeInterface) return Carbon::instance($raw);
        if (is_string($raw) && ! empty($raw)) {
            try { return Carbon::parse($raw); } catch (\Exception) {}
        }
        return now();
    }
}
