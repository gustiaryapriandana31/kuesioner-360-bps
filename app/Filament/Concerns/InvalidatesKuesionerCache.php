<?php

// Trait untuk invalidasi cache kuesioner dan pegawai setelah mutasi data di Filament.

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Cache;

trait InvalidatesKuesionerCache
{
    /**
     * Hapus semua cache yang berkaitan dengan daftar dan detail kuesioner.
     * Dipanggil setelah create/edit/delete kuesioner.
     */
    protected function flushKuesionerCache(?string $kode = null): void
    {
        // Daftar kuesioner di halaman index
        Cache::forget('kuesioners:list:active_closed');

        // Cache detail kuesioner spesifik (jika kode diketahui)
        if ($kode) {
            Cache::forget("kuesioner:kode:{$kode}:detail");
            Cache::forget("kuesioner:kode:{$kode}:with_pertanyaans");
        }
    }

    /**
     * Hapus semua cache yang berkaitan dengan daftar pegawai.
     * Dipanggil setelah create/edit/delete pegawai.
     */
    protected function flushPegawaiCache(): void
    {
        // Cache global daftar pegawai assessable (dipakai oleh show())
        Cache::forget('pegawais:active:assessable:ordered');

        // Cache total pegawai per user: kita tidak tahu semua userId,
        // tapi TTL 10 menit sudah cukup pendek — biarkan expire sendiri.
        // Untuk akurasi instan, simpan daftar userIds yang punya cache aktif.
        $trackedUserIds = Cache::get('pegawais:cache:tracked_user_ids', []);
        foreach ($trackedUserIds as $uid) {
            Cache::forget("pegawais:total:assessable:exclude:{$uid}");
        }
        Cache::forget('pegawais:cache:tracked_user_ids');
    }
}
