<?php

// Halaman edit pegawai pada resource Filament Pegawai.

namespace App\Filament\Resources\PegawaiResource\Pages;

use App\Filament\Concerns\InvalidatesKuesionerCache;
use App\Filament\Resources\PegawaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPegawai extends EditRecord
{
    use InvalidatesKuesionerCache;

    protected static string $resource = PegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->flushPegawaiCache();
    }
}
