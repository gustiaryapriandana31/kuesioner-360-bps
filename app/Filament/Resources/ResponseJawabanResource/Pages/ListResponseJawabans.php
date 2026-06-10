<?php

// Halaman daftar seluruh response jawaban di admin Filament.

namespace App\Filament\Resources\ResponseJawabanResource\Pages;

use App\Filament\Resources\ResponseJawabanResource;
use Filament\Resources\Pages\ListRecords;

class ListResponseJawabans extends ListRecords
{
    protected static string $resource = ResponseJawabanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
