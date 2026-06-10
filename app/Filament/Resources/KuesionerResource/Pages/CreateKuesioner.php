<?php

// Halaman pembuatan kuesioner pada resource Filament Kuesioner.

namespace App\Filament\Resources\KuesionerResource\Pages;

use App\Filament\Concerns\InvalidatesKuesionerCache;
use App\Filament\Resources\KuesionerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKuesioner extends CreateRecord
{
    use InvalidatesKuesionerCache;

    protected static string $resource = KuesionerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }


}
