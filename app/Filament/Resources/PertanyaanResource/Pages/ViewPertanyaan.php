<?php

// Halaman detail pertanyaan kuesioner di admin Filament.

namespace App\Filament\Resources\PertanyaanResource\Pages;

use App\Filament\Resources\PertanyaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPertanyaan extends ViewRecord
{
    protected static string $resource = PertanyaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
