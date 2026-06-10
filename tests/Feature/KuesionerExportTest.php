<?php

namespace Tests\Feature;

use App\Models\Kuesioner;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuesionerExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed data dasar
        $this->artisan('db:seed');
    }

    public function test_authenticated_pegawai_can_export_kuesioner_to_excel()
    {
        // Buat user pegawai biasa (non-admin)
        $user = User::create([
            'name' => 'Pegawai Test',
            'email' => 'pegawaitest@gmail.com',
            'nip' => '199001012015011002',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        Pegawai::create([
            'user_id' => $user->id,
            'nip' => $user->nip,
            'nama' => $user->name,
            'jabatan' => 'Staff IT',
            'departemen' => 'IPDS',
            'is_active' => true,
        ]);

        // Ambil kuesioner
        $kuesioner = Kuesioner::query()->first();

        // Panggil route export
        $response = $this->actingAs($user)
            ->get(route('kuesioner.export', $kuesioner));

        // Pastikan status 200 dan mengembalikan file excel download
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        $cleanJudul = str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>', ' '], '_', $kuesioner->judul);
        $expectedFileName = "Export_Penilaian360_{$cleanJudul}_" . now()->format('Y-m-d') . ".xlsx";
        
        $response->assertHeader('content-disposition', 'attachment; filename=' . $expectedFileName);
    }
}
