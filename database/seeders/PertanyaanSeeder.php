<?php

// Seeder untuk membuat delapan pertanyaan penilaian Kuesioner 360.

namespace Database\Seeders;

use App\Models\Kuesioner;
use Illuminate\Database\Seeder;

class PertanyaanSeeder extends Seeder
{
    /**
     * Seed pertanyaan panjang dan realistis dengan judul dan isi untuk kuesioner TW1-2025.
     */
    public function run(): void
    {
        $kuesioner = Kuesioner::query()->firstOrFail();

        $questions = [
            [
                'judul' => 'Pegawai Paling BERORIENTASI LAYANAN',
                'isi' => "v  Memahami dan memenuhi kebutuhan Masyarakat\nv  Ramah, cekatan, solutif, dan dapat diandalkan\nv  Melakukan perbaikan tiada henti",
            ],
            [
                'judul' => 'Pegawai Paling AKUNTABEL',
                'isi' => "v  Melaksanakan tugas dengan jujur, bertanggung jawab, cermat, serta disiplin dan berintegritas tinggi\nv  Menggunakan kekayaan dan barang milik negara secara bertanggung jawab, efektif dan efisien\nv  Tidak menyalahgunakan kewenangan jabatan",
            ],
            [
                'judul' => 'Pegawai Paling KOMPETEN',
                'isi' => "v  Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah\nv  Membantu orang lain belajar\nv  Melaksanakan tugas dengan kualitas terbaik",
            ],
            [
                'judul' => 'Pegawai Paling HARMONIS',
                'isi' => "v  Menghargai setiap orang apapun latar belakangnya\nv  Suka menolong orang lain\nv  Membangun lingkungan kerja yang kondusif",
            ],
            [
                'judul' => 'Pegawai Paling LOYAL',
                'isi' => "v  Memegang teguh ideologi Pancasila dan Undang-Undang Dasar Negara Republik Indonesia Tahun 1945\nv  Setia kepada NKRI serta pemerintah yang sah\nv  Menjaga nama baik sesama ASN, pimpinan, instansi dan negara, serta menjaga rahasia jabatan dan negara",
            ],
            [
                'judul' => 'Pegawai Paling ADAPTIF',
                'isi' => "v  Cepat menyesuaikan diri menghadapi perubahan\nv  Terus berinovasi dan mengembangkan kreativitas\nv  Bertindak proaktif",
            ],
            [
                'judul' => 'Pegawai Paling KOLABORATIF',
                'isi' => "v  Memberikan kesempatan kepada berbagai pihak untuk berkontribusi\nv  Terbuka dalam bekerja sama untuk menghasilkan nilai tambah\nv  Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama",
            ],
            [
                'judul' => 'Budaya Organisasi',
                'isi' => "1. Be a Leader Not a Boss (Selalu menjadi telatan, memberikan semangat dalam tim, dan mendorong perkembangan untuk mencapai tujuan bersama-sama)\n2. Inovasi Tanpa Henti di Setiap Lini (Selalu menerapkan inovasi tanpa henti)\n3. Komunikasi, Koordinasi dan Diplomasi (Selalu menjaga komunikasi, koordinas dan diplomasi yang dilaksanakan secara berjenjang pada setiap tingkatan dan bertanggung jawab sesuai dengan lingkup tugas dan kewenangan)\n4. Kualitas data dan Proses Bisnis (Selalu berkomitmen untuk menghasilkan dan menjaga kualitas data yang akurat dan terpercaya)\n5. Kerja Keras dan Kerja Cerdas (Selalu semangat kerja keras dengan strategi kerja cerdas)",
            ],
        ];

        foreach ($questions as $index => $qData) {
            $kuesioner->pertanyaans()->updateOrCreate(
                ['urutan' => $index + 1],
                [
                    'judul' => $qData['judul'],
                    'isi' => $qData['isi'],
                    'poin_min' => 1,
                    'poin_max' => 10,
                    'is_active' => true,
                ],
            );
        }
    }
}
