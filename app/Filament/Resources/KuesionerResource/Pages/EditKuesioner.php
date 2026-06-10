<?php

// Halaman edit kuesioner dan kelola pertanyaan relasi.

namespace App\Filament\Resources\KuesionerResource\Pages;

use App\Filament\Concerns\InvalidatesKuesionerCache;
use App\Filament\Resources\KuesionerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKuesioner extends EditRecord
{
    use InvalidatesKuesionerCache;

    protected static string $resource = KuesionerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Invalidasi cache spesifik kuesioner yang diedit berdasarkan kode-nya
        $this->flushKuesionerCache($this->record->kode);
    }
}
