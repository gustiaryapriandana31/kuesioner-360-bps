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

class Penilaian360Export implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $kuesioner;
    protected $activeTargets;
    protected $responses;

    public function __construct(Kuesioner $kuesioner)
    {
        $this->kuesioner = $kuesioner;

        $excludedIds = $kuesioner->excluded_pegawai_ids ?? [];

        // Logika Target Aktif: semua pegawai dikurangi yang ada di excluded_target_ids, diurutkan target_id ASC
        $this->activeTargets = Pegawai::query()
            ->active()
            ->assessable()
            ->whereNotIn('id', $excludedIds)
            ->orderBy('id', 'asc')
            ->get();

        // Ambil semua responses untuk kuesioner ini
        $this->responses = Response::query()
            ->where('kuesioner_id', $this->kuesioner->id)
            ->with(['penilai.pegawai', 'jawabans.pertanyaan'])
            ->get();
    }

    /**
     * Menyusun data penilai dan jawaban untuk baris-baris Excel.
     */
    public function array(): array
    {
        $n = count($this->activeTargets);
        if ($n === 0) {
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

        // Ambil list penilai unik (dari response), urutkan nama penilai A-Z
        $penilais = $this->responses->map(fn($r) => $r->penilai)->filter()->unique('id');
        $penilais = $penilais->sortBy(function ($user) {
            return $user->pegawai?->nama ?? $user->name;
        })->values();

        // Urutan 6 kompetensi (sesuai nomor urutan pertanyaan di database)
        $competencies = [
            'BERORIENTASI LAYANAN' => 1,
            'AKUNTABEL' => 2,
            'KOMPETEN' => 3,
            'LOYAL' => 5,
            'ADAPTIF' => 6,
            'KOLABORATIF' => 7,
        ];

        $data = [];
        foreach ($penilais as $penilai) {
            $penilaiId = $penilai->id;
            $penilaiName = $penilai->pegawai?->nama ?? $penilai->name;

            $row = [$penilaiName];

            foreach ($competencies as $comp => $urutan) {
                foreach ($this->activeTargets as $target) {
                    $nilai = $lookup[$penilaiId][$target->id][$urutan] ?? '';
                    $row[] = $nilai;
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Mendefinisikan header 2 baris.
     */
    public function headings(): array
    {
        $n = count($this->activeTargets);
        if ($n === 0) {
            return [['Nama Penilai']];
        }

        $competencies = [
            'BERORIENTASI LAYANAN',
            'AKUNTABEL',
            'KOMPETEN',
            'LOYAL',
            'ADAPTIF',
            'KOLABORATIF',
        ];

        // Baris 1: Nama Kompetensi diulang sebanyak target aktif (n) agar bisa di-merge
        $row1 = ['Nama Penilai'];
        foreach ($competencies as $comp) {
            for ($i = 0; $i < $n; $i++) {
                $row1[] = $comp;
            }
        }

        // Baris 2: Nama-nama target aktif diulang untuk setiap kompetensi
        $row2 = ['']; // A2 kosong karena digabung dengan A1
        foreach ($competencies as $comp) {
            foreach ($this->activeTargets as $target) {
                $row2[] = $target->nama;
            }
        }

        return [$row1, $row2];
    }

    /**
     * Formatting dan style Excel.
     */
    public function styles(Worksheet $sheet)
    {
        $n = count($this->activeTargets);
        $totalCols = 1 + 6 * $n;
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $styles = [
            // Header Baris 1 (Kompetensi): Bold, warna biru muda (#BDD7EE), center
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFBDD7EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Header Baris 2 (Target): Bold, warna abu-abu (#D9D9D9), center, wrap text
            2 => [
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

        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 3) {
            // Kolom Nama Penilai (A): Left align
            $sheet->getStyle("A3:A{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Semua kolom nilai (B ke kanan): Center alignment
            $sheet->getStyle("B3:{$lastCol}{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return $styles;
    }

    /**
     * Lebar kolom minimal.
     */
    public function columnWidths(): array
    {
        // Lebar kolom nama penilai minimal 25
        $widths = ['A' => 25];

        // Lebar kolom nilai minimal 12
        $n = count($this->activeTargets);
        $totalCols = 1 + 6 * $n;
        for ($i = 2; $i <= $totalCols; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $widths[$colLetter] = 12;
        }

        return $widths;
    }

    /**
     * Event untuk merge cell dan freeze pane.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze pane agar Kolom A ("Nama Penilai") & Baris 1-2 tetap terlihat saat di-scroll
                $sheet->freezePane('B3');

                $n = count($this->activeTargets);
                if ($n > 0) {
                    // Merge cell A1:A2 untuk "Nama Penilai"
                    $sheet->mergeCells('A1:A2');
                    $sheet->getStyle('A1:A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Merge cell baris 1 untuk setiap kompetensi sebanyak jumlah target aktif (n)
                    for ($i = 0; $i < 6; $i++) {
                        $startColIndex = 2 + ($i * $n);
                        $endColIndex = 1 + (($i + 1) * $n);

                        $startCol = Coordinate::stringFromColumnIndex($startColIndex);
                        $endCol = Coordinate::stringFromColumnIndex($endColIndex);

                        $sheet->mergeCells("{$startCol}1:{$endCol}1");
                    }
                }
            }
        ];
    }
}
