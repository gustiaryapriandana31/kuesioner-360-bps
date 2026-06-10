<?php

// Halaman pembuatan pegawai pada resource Filament Pegawai.

namespace App\Filament\Resources\PegawaiResource\Pages;

use App\Filament\Concerns\InvalidatesKuesionerCache;
use App\Filament\Resources\PegawaiResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePegawai extends CreateRecord
{
    use InvalidatesKuesionerCache;

    protected static string $resource = PegawaiResource::class;

    protected function afterCreate(): void
    {
        $this->flushPegawaiCache();
    }
}
