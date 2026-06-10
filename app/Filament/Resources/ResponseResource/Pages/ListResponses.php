<?php

// Halaman daftar monitoring response kuesioner.

namespace App\Filament\Resources\ResponseResource\Pages;

use App\Filament\Resources\ResponseResource;
use Filament\Resources\Pages\ListRecords;

class ListResponses extends ListRecords
{
    protected static string $resource = ResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
