<?php

namespace Tests\Feature;

use App\Models\Kuesioner;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\Response;
use App\Models\ResponseJawaban;
use App\Exports\Penilaian360Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
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

    public function test_export_contains_correct_headers_and_sorting()
    {
        Excel::fake();

        $kuesioner = Kuesioner::query()->first();

        // Bersihkan data responses bawaan seed agar kita bisa kontrol sepenuhnya
        Response::query()->truncate();
        ResponseJawaban::query()->truncate();

        // Ambil pegawai pre-seeded (Maria Ulfa & Akhmad Riza)
        $pegawai1 = Pegawai::where('nama', 'LIKE', '%Maria Ulfa%')->firstOrFail();
        $user1 = $pegawai1->user;

        $pegawai2 = Pegawai::where('nama', 'LIKE', '%Akhmad Riza%')->firstOrFail();
        $user2 = $pegawai2->user;

        $t1 = now()->subMinutes(10);
        $t2 = now();

        // Buat respon dengan submitted_at berbeda (Riza lebih lambat daripada Maria)
        $resp1 = Response::create([
            'kuesioner_id' => $kuesioner->id,
            'penilai_id' => $user1->id,
            'target_id' => $pegawai2->id,
            'status' => 'submitted',
            'submitted_at' => $t1
        ]);

        $resp2 = Response::create([
            'kuesioner_id' => $kuesioner->id,
            'penilai_id' => $user2->id,
            'target_id' => $pegawai1->id,
            'status' => 'submitted',
            'submitted_at' => $t2
        ]);

        // Panggil export
        $export = new Penilaian360Export($kuesioner);
        $headings = $export->headings();
        $data = $export->array();

        // 1. Verifikasi Heading baris pertama
        $this->assertCount(1, $headings);
        $headerRow = $headings[0];

        // Kolom pertama dan kedua: Timestamp, Nama Penilai
        $this->assertEquals('Timestamp', $headerRow[0]);
        $this->assertEquals('Nama Penilai', $headerRow[1]);

        // Kolom ketiga dst harus mengandung format: [Judul]*\n\n[Isi] [index. Nama]
        // Riza ada di index 1, Maria di index 2
        $this->assertStringContainsString('[1. Akhmad Riza, S.E., M.M]', $headerRow[2]);
        $this->assertStringContainsString('[2. Maria Ulfa, S.ST]', $headerRow[3]);

        // 2. Verifikasi Data rows diurutkan berdasarkan submitted_at (Maria lebih dulu karena $t1)
        // Row 0: Maria (Timestamp, Nama, Nilai...)
        // Row 1: Riza (Timestamp, Nama, Nilai...)
        // Row 2: Total Row (Timestamp = '', Nama = 'Total', SUM...)
        $this->assertCount(3, $data); // 2 data rows + 1 total row

        // Baris 1: Maria
        $this->assertEquals($t1->format('d/m/Y H:i:s'), $data[0][0]);
        $this->assertEquals($pegawai1->nama, $data[0][1]);

        // Baris 2: Riza
        $this->assertEquals($t2->format('d/m/Y H:i:s'), $data[1][0]);
        $this->assertEquals($pegawai2->nama, $data[1][1]);

        // Baris 3: Total Row
        $this->assertEquals('', $data[2][0]);
        $this->assertEquals('Total', $data[2][1]);
        $this->assertEquals('=SUM(C2:C3)', $data[2][2]);
    }
}
