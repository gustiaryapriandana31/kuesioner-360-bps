<?php

namespace Tests\Feature;

use App\Models\Kuesioner;
use App\Models\Pertanyaan;
use App\Models\User;
use App\Services\KuesionerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuesionerCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_kuesioner_can_be_copied_to_next_triwulan()
    {
        // 1. Arrange: Create user, kuesioner, and questions
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'nip' => '1234567890',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $source = Kuesioner::create([
            'kode' => 'TW1-2025',
            'judul' => 'Kuesioner TW1 2025',
            'deskripsi' => 'Deskripsi kuesioner sumber',
            'triwulan' => 1,
            'tahun' => 2025,
            'status' => 'active',
            'created_by' => $admin->id,
            'excluded_pegawai_ids' => [1, 2, 3],
            'assign_all' => false,
        ]);

        $p1 = Pertanyaan::create([
            'judul' => 'Pertanyaan 1',
            'isi' => 'Isi Pertanyaan 1',
            'urutan' => 1,
            'poin_min' => 1,
            'poin_max' => 10,
            'is_active' => true,
        ]);

        $p2 = Pertanyaan::create([
            'judul' => 'Pertanyaan 2',
            'isi' => 'Isi Pertanyaan 2',
            'urutan' => 2,
            'poin_min' => 1,
            'poin_max' => 10,
            'is_active' => true,
        ]);

        $source->pertanyaans()->attach([$p1->id, $p2->id]);

        // 2. Act: Call service to copy
        $this->actingAs($admin);
        $service = new KuesionerService();
        $copy = $service->copyFromTW($source);

        // 3. Assert: Verify the copied kuesioner attributes and relationship
        $this->assertDatabaseHas('kuesioners', [
            'id' => $copy->id,
            'kode' => 'TW2-2025',
            'judul' => 'Kuesioner 360 TW2 2025',
            'deskripsi' => 'Deskripsi kuesioner sumber',
            'triwulan' => 2,
            'tahun' => 2025,
            'copied_from_id' => $source->id,
            'status' => 'active',
            'assign_all' => 0,
        ]);

        $this->assertEquals([], $copy->excluded_pegawai_ids);
        $this->assertEquals(2, $copy->pertanyaans()->count());
        $this->assertTrue($copy->pertanyaans->contains($p1));
        $this->assertTrue($copy->pertanyaans->contains($p2));
    }
}
