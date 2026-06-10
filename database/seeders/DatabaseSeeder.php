<?php

// Seeder utama yang memanggil data awal Kuesioner 360.

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PegawaiSeeder::class,
            KuesionerSeeder::class,
            PertanyaanSeeder::class,
        ]);
    }
}
