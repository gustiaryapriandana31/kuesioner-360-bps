<?php

// Halaman edit pertanyaan kuesioner di admin Filament.

namespace App\Filament\Resources\PertanyaanResource\Pages;

use App\Filament\Concerns\InvalidatesKuesionerCache;
use App\Filament\Resources\PertanyaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPertanyaan extends EditRecord
{
    use InvalidatesKuesionerCache;

    protected static string $resource = PertanyaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->after(fn () => $this->flushKuesionerCache($this->record->kuesioner?->kode)),
            Actions\RestoreAction::make()
                ->after(fn () => $this->flushKuesionerCache($this->record->kuesioner?->kode)),
            Actions\ForceDeleteAction::make()
                ->after(fn () => $this->flushKuesionerCache($this->record->kuesioner?->kode)),
        ];
    }

    protected function afterSave(): void
    {
        $this->flushKuesionerCache($this->record->kuesioner?->kode);
    }
}
