<?php

// Halaman pembuatan pertanyaan kuesioner di admin Filament.

namespace App\Filament\Resources\PertanyaanResource\Pages;

use App\Filament\Concerns\InvalidatesKuesionerCache;
use App\Filament\Resources\PertanyaanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePertanyaan extends CreateRecord
{
    use InvalidatesKuesionerCache;

    protected static string $resource = PertanyaanResource::class;

    protected function afterCreate(): void
    {
        $this->flushKuesionerCache($this->record->kuesioner?->kode);
    }
}
