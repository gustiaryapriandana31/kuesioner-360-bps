<?php

// Seeder untuk membuat kuesioner aktif awal TW1-2025.

namespace Database\Seeders;

use App\Models\Kuesioner;
use App\Models\User;
use Illuminate\Database\Seeder;

class KuesionerSeeder extends Seeder
{
    /**
     * Seed satu kuesioner aktif untuk TW1 tahun 2025.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'adminkuesioner@gmail.com')->firstOrFail();

        Kuesioner::query()->updateOrCreate(
            ['kode' => 'TW1-2026'],
            [
                'judul' => 'Kuesioner 360 TW1 2026',
                'deskripsi' => 'Penilaian periode TW1 2026 untuk kedisiplinan, integritas, kinerja, dan kolaborasi pegawai BPS Kabupaten Ogan Ilir.',
                'triwulan' => 1,
                'tahun' => 2026,
                'copied_from_id' => null,
                'status' => 'active',
                'dibuka_pada' => now()->subDays(7),
                'ditutup_pada' => now()->addDays(21),
                'created_by' => $admin->id,
            ],
        );
    }
}
