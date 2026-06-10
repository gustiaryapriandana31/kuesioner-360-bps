<?php

// Relation manager Filament untuk mengelola pertanyaan di dalam kuesioner.

namespace App\Filament\Resources\KuesionerResource\RelationManagers;

use Illuminate\Support\Facades\Cache;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PertanyaanRelationManager extends RelationManager
{
    protected static string $relationship = 'pertanyaans';

    protected static ?string $title = 'Pertanyaan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('template_pertanyaan')
                    ->label('Pilih dari Pertanyaan yang Sudah Ada (Opsional)')
                    ->options(function () {
                        return \App\Models\Pertanyaan::query()
                            ->select(['id', 'judul', 'isi'])
                            ->distinct()
                            ->get()
                            ->mapWithKeys(fn($p) => [$p->id => $p->judul . ' - ' . \Illuminate\Support\Str::limit($p->isi, 80)])
                            ->toArray();
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $p = \App\Models\Pertanyaan::find($state);
                            if ($p) {
                                $set('judul', $p->judul);
                                $set('isi', $p->isi);
                            }
                        }
                    })
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->helperText('Pilih pertanyaan yang sudah pernah ada untuk menyalin judul dan isinya secara otomatis.'),
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('isi')
                    ->required()
                    ->rows(4)
                    ->helperText('Enter/baris baru akan tampil di halaman kuesioner. Bisa gunakan penanda seperti 1., -, >, atau • di awal baris.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('urutan')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('poin_min')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('poin_max')
                    ->numeric()
                    ->default(10)
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('judul')
            ->defaultSort('urutan')
            ->reorderable('urutan')
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('judul')
                    ->limit(40)
                    ->sortable(),
                Tables\Columns\TextColumn::make('isi')
                    ->limit(80)
                    ->wrap(),
                Tables\Columns\TextColumn::make('poin_min'),
                Tables\Columns\TextColumn::make('poin_max'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(fn() => $this->invalidateOwnerKuesionerCache()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn() => $this->invalidateOwnerKuesionerCache()),
                Tables\Actions\DetachAction::make()
                    ->label('Lepas')
                    ->after(fn() => $this->invalidateOwnerKuesionerCache()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn() => $this->invalidateOwnerKuesionerCache()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Lepas Terpilih')
                        ->after(fn() => $this->invalidateOwnerKuesionerCache()),
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(fn() => $this->invalidateOwnerKuesionerCache()),
                ]),
            ]);
    }

    /**
     * Invalidasi cache kuesioner induk ketika pertanyaan berubah.
     * Kode kuesioner diambil dari record induk (owner) RelationManager.
     */
    private function invalidateOwnerKuesionerCache(): void
    {
        $kode = $this->getOwnerRecord()?->kode;

        if ($kode) {
            Cache::forget("kuesioner:kode:{$kode}:detail");
            Cache::forget("kuesioner:kode:{$kode}:with_pertanyaans");
        }

        // Invalidasi juga daftar kuesioner (pertanyaans_count berubah)
        Cache::forget('kuesioners:list:active_closed');
    }
}
