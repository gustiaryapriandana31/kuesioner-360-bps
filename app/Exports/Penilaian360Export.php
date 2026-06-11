<?php

namespace App\Exports;

use App\Models\Kuesioner;
use App\Models\Pegawai;
use App\Models\Response;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Penilaian360Export implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $kuesioner;
    protected $activeTargets;
    protected $responses;
    protected $questions;
    protected $pegawaiIndexMap = [];
    protected $targetHeaderNames = [];

    public function __construct(Kuesioner $kuesioner)
    {
        $this->kuesioner = $kuesioner;

        $excludedIds = $kuesioner->excluded_pegawai_ids ?? [];

        // Ambil semua pegawai aktif assessable yang tidak dikecualikan
        $allActiveTargets = Pegawai::query()
            ->active()
            ->assessable()
            ->whereNotIn('id', $excludedIds)
            ->get();

        // Daftar urutan nama pegawai dari instruksi user
        $orderedNames = [
            1 => 'Akhmad Riza, S.E., M.M',
            2 => 'Maria Ulfa, S.ST',
            3 => 'Arie Feazri, S.E., M.Si',
            4 => 'Ifone Arma, S.E., M.M',
            5 => 'Indra Gunawan, S.E',
            6 => 'Farhan Segentar Alam, S.E., M.M',
            7 => 'Kurniasih, S.ST',
            8 => 'Achmad Awaluddin, S.P., M.E',
            9 => 'Guntur Teguh Iman, S.E., M.Si',
            10 => 'Sutarso, S.T.',
            11 => 'Fahria, S.ST, M.Si',
            12 => 'Budi Martha, S.E',
            13 => 'Rismawaty, S.ST, M.E.K.K',
            14 => 'Risma Karlia, S.ST',
            15 => 'Ishlahul Kamal, S.Si',
            16 => 'Lidia Anggita Putri, S.ST',
            17 => 'Pusvitasari, S.Sos, M.P',
            18 => 'Yurahadi, S.E',
            19 => 'Aisyah Puteri Utama, S.Tr. Stat',
            20 => 'Efran Feri Kriswanto, S.ST',
            21 => 'Juarsah, S.E',
            22 => 'Sulastri, S.Sos',
            23 => 'Indah Dwi Pebrianti, S.Si.',
            24 => 'Rosmilyani, S.M',
            25 => 'Dea Anisa Irawan, S.Tr.Stat.',
            26 => 'Meita Ayudhia, S.E., M.P',
            27 => 'Yulis Nurhayani, S.E',
            28 => 'Cecep Nopriansyah, A.Md',
            29 => 'Ani Yuningsih, A. Md.',
            30 => 'Moh. Reza Bahusin',
            31 => 'Astri, A.Md',
            32 => 'Ade Ulfa Wahyuni, A.Md',
            33 => 'Hendra Febrianto, A.Md',
            34 => 'Sari Ratna Dewi, S.Si',
            35 => 'Sapik',
            36 => 'Irmalina',
            37 => 'Rahmadi',
            38 => 'Ferdian',
            39 => 'Rian Maulana Saputra',
        ];

        // Fuzzy matching in-memory untuk mengurutkan active targets
        $allPegawais = Pegawai::all();
        $targetMap = [];
        foreach ($allActiveTargets as $target) {
            $targetMap[$target->id] = $target;
        }

        $orderedTargets = [];
        $matchedIds = [];
        $pegawaiIndexMap = [];
        $targetHeaderNames = [];

        foreach ($orderedNames as $idx => $name) {
            $matchedPegawai = null;
            $namaBersih = trim(preg_replace('/\s+/', ' ', $name));
            $namaBersihLower = mb_strtolower($namaBersih);
            
            // Level 1: Exact match case-insensitive
            foreach ($allPegawais as $p) {
                if (mb_strtolower($p->nama) === $namaBersihLower) {
                    $matchedPegawai = $p;
                    break;
                }
            }
            
            // Level 2: Normalisasi spasi di sekitar tanda baca
            if (!$matchedPegawai) {
                $namaNorm = trim(preg_replace('/\s+/', ' ', preg_replace('/\s*([,.])\s*/', '$1 ', $namaBersihLower)));
                foreach ($allPegawais as $p) {
                    $candidateNorm = trim(preg_replace('/\s+/', ' ', preg_replace('/\s*([,.])\s*/', '$1 ', mb_strtolower($p->nama))));
                    if ($candidateNorm === $namaNorm) {
                        $matchedPegawai = $p;
                        break;
                    }
                }
            }
            
            // Level 3: LIKE pada nama dasar (sebelum koma pertama)
            if (!$matchedPegawai) {
                $namaDasar = trim(explode(',', $namaBersihLower)[0]);
                if (!empty($namaDasar)) {
                    foreach ($allPegawais as $p) {
                        $pDasar = mb_strtolower($p->nama);
                        if (str_contains($pDasar, $namaDasar)) {
                            $matchedPegawai = $p;
                            break;
                        }
                    }
                }
            }
            
            // Level 4: similar_text >= 75%
            if (!$matchedPegawai) {
                $best = null;
                $bestScore = 0;
                foreach ($allPegawais as $p) {
                    similar_text($namaBersihLower, mb_strtolower($p->nama), $pct);
                    if ($pct >= 75 && $pct > $bestScore) {
                        $bestScore = $pct;
                        $best = $p;
                    }
                }
                $matchedPegawai = $best;
            }
            
            if ($matchedPegawai) {
                $pegawaiIndexMap[$matchedPegawai->id] = $idx;
                $targetHeaderNames[$matchedPegawai->id] = $name;
                if (isset($targetMap[$matchedPegawai->id])) {
                    $orderedTargets[] = $targetMap[$matchedPegawai->id];
                    $matchedIds[$matchedPegawai->id] = true;
                }
            }
        }

        // Jika ada target aktif yang tidak ada di list 39, masukkan di akhir
        $nextIdx = 40;
        foreach ($allActiveTargets as $target) {
            if (!isset($matchedIds[$target->id])) {
                $orderedTargets[] = $target;
                $pegawaiIndexMap[$target->id] = $nextIdx++;
                $targetHeaderNames[$target->id] = $target->nama;
            }
        }

        $this->activeTargets = collect($orderedTargets);
        $this->pegawaiIndexMap = $pegawaiIndexMap;
        $this->targetHeaderNames = $targetHeaderNames;

        // Ambil semua responses untuk kuesioner ini
        $this->responses = Response::query()
            ->where('kuesioner_id', $this->kuesioner->id)
            ->with(['penilai.pegawai', 'jawabans.pertanyaan'])
            ->get();

        // Ambil list pertanyaan aktif kuesioner secara dinamis
        $this->questions = $this->kuesioner->pertanyaans()
            ->orderBy('urutan', 'asc')
            ->get();
    }

    /**
     * Menyusun data penilai dan jawaban untuk baris-baris Excel.
     */
    public function array(): array
    {
        $n = count($this->activeTargets);
        if ($n === 0 || count($this->questions) === 0) {
            return [];
        }

        // Simpan ke lookup array untuk akses O(1)
        $lookup = [];
        foreach ($this->responses as $resp) {
            $penilaiId = $resp->penilai_id;
            $targetId = $resp->target_id;
            foreach ($resp->jawabans as $jawaban) {
                $urutan = $jawaban->pertanyaan?->urutan;
                if ($urutan) {
                    $lookup[$penilaiId][$targetId][$urutan] = $jawaban->nilai;
                }
            }
        }

        // Ambil list penilai unik (dari response)
        $penilais = $this->responses->map(fn($r) => $r->penilai)->filter()->unique('id');

        // Buat map dari penilai_id ke submitted_at paling awal
        $penilaiSubmittedAt = [];
        foreach ($this->responses as $resp) {
            $penilaiId = $resp->penilai_id;
            if ($resp->submitted_at) {
                if (!isset($penilaiSubmittedAt[$penilaiId]) || $resp->submitted_at->lt($penilaiSubmittedAt[$penilaiId])) {
                    $penilaiSubmittedAt[$penilaiId] = $resp->submitted_at;
                }
            }
        }

        // Urutkan penilai berdasarkan submitted_at (paling awal dulu). 
        // Jika submitted_at kosong (misal draft), diletakkan paling belakang.
        $penilais = $penilais->sortBy(function ($user) use ($penilaiSubmittedAt) {
            $submittedAt = $penilaiSubmittedAt[$user->id] ?? null;
            return $submittedAt ? $submittedAt->timestamp : PHP_INT_MAX;
        })->values();

        $data = [];
        foreach ($penilais as $penilai) {
            $penilaiId = $penilai->id;
            $penilaiName = $penilai->pegawai?->nama ?? $penilai->name;
            $submittedAt = $penilaiSubmittedAt[$penilaiId] ?? null;
            $timestampStr = $submittedAt ? $submittedAt->format('d/m/Y H:i:s') : '';

            $row = [$timestampStr, $penilaiName];

            foreach ($this->questions as $pertanyaan) {
                $urutan = $pertanyaan->urutan;
                foreach ($this->activeTargets as $target) {
                    $nilai = $lookup[$penilaiId][$target->id][$urutan] ?? '';
                    $row[] = $nilai;
                }
            }

            $data[] = $row;
        }

        // Tambahkan baris TOTAL di paling bawah
        $totalRow = ['', 'Total'];
        $numQuestions = count($this->questions);
        $totalCols = 2 + $numQuestions * $n;
        $lastDataRow = count($penilais) + 1;

        for ($i = 3; $i <= $totalCols; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $totalRow[] = "=SUM({$colLetter}2:{$colLetter}{$lastDataRow})";
        }

        $data[] = $totalRow;

        return $data;
    }

    /**
     * Mendefinisikan header.
     */
    public function headings(): array
    {
        $n = count($this->activeTargets);
        if ($n === 0) {
            return [['Timestamp', 'Nama Penilai']];
        }

        $headers = ['Timestamp', 'Nama Penilai'];

        foreach ($this->questions as $pertanyaan) {
            // Tambahkan tanda * di akhir judul jika belum ada
            $judul = $pertanyaan->judul;
            if (!str_ends_with($judul, '*')) {
                $judul .= '*';
            }

            foreach ($this->activeTargets as $target) {
                $idx = $this->pegawaiIndexMap[$target->id] ?? '';
                $targetName = $this->targetHeaderNames[$target->id] ?? $target->nama;

                // Format: Judul* \n\n Isi [index. Nama Pegawai]
                $headers[] = $judul . "\n\n" . $pertanyaan->isi . " [" . $idx . ". " . $targetName . "]";
            }
        }

        return [$headers];
    }

    /**
     * Formatting dan style Excel.
     */
    public function styles(Worksheet $sheet)
    {
        $numQuestions = count($this->questions);
        $n = count($this->activeTargets);
        $totalCols = 2 + $numQuestions * $n;
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $styles = [
            // Header Baris 1: Bold, warna abu-abu (#D9D9D9), center, wrap text
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD9D9D9']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],
        ];

        // Set row height for header to fit multi-line questions
        $sheet->getRowDimension(1)->setRowHeight(95);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 2) {
            // Kolom Timestamp (A) & Nama Penilai (B): Left align
            $sheet->getStyle("A2:B{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Semua kolom nilai (C ke kanan): Center alignment
            $sheet->getStyle("C2:{$lastCol}{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Style total row (paling bawah)
            $sheet->getStyle("A{$highestRow}:{$lastCol}{$highestRow}")
                ->getFont()
                ->setBold(true);

            $sheet->getStyle("A{$highestRow}:{$lastCol}{$highestRow}")
                ->getBorders()
                ->getTop()
                ->setBorderStyle(Border::BORDER_THIN);

            $sheet->getStyle("A{$highestRow}:{$lastCol}{$highestRow}")
                ->getBorders()
                ->getBottom()
                ->setBorderStyle(Border::BORDER_DOUBLE);
        }

        return $styles;
    }

    /**
     * Lebar kolom.
     */
    public function columnWidths(): array
    {
        // Lebar kolom
        $widths = [
            'A' => 20, // Timestamp
            'B' => 25, // Nama Penilai
        ];

        // Lebar kolom nilai minimal 35
        $numQuestions = count($this->questions);
        $n = count($this->activeTargets);
        $totalCols = 2 + $numQuestions * $n;
        for ($i = 3; $i <= $totalCols; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $widths[$colLetter] = 35;
        }

        return $widths;
    }

    /**
     * Event untuk freeze pane.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze pane agar Kolom A (Timestamp) & B (Nama Penilai) & Baris 1 tetap terlihat saat di-scroll
                $sheet->freezePane('C2');
            }
        ];
    }
}
