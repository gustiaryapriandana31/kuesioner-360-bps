<?php

// Resource Filament untuk menampilkan seluruh jawaban detail dari response kuesioner.

namespace App\Filament\Resources;

use App\Filament\Resources\ResponseJawabanResource\Pages;
use App\Models\ResponseJawaban;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResponseJawabanResource extends Resource
{
    protected static ?string $model = ResponseJawaban::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Kuesioner 360';

    protected static ?string $modelLabel = 'Response Jawaban';

    protected static ?string $pluralModelLabel = 'Response Jawaban';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Jawaban')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Jawaban ID'),
                        Infolists\Components\TextEntry::make('response.id')
                            ->label('Response ID'),
                        Infolists\Components\TextEntry::make('nilai')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state <= 3 => 'danger',
                                $state <= 6 => 'warning',
                                default => 'success',
                            }),
                        Infolists\Components\TextEntry::make('pertanyaan.urutan')
                            ->label('No Soal')
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('pertanyaan.judul')
                            ->label('Judul Pertanyaan')
                            ->placeholder('Pertanyaan tidak tersedia'),
                        Infolists\Components\TextEntry::make('pertanyaan.isi')
                            ->label('Isi Pertanyaan')
                            ->placeholder('Detail pertanyaan tidak tersedia')
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ]),

                Infolists\Components\Section::make('Konteks Response')
                    ->schema([
                        Infolists\Components\TextEntry::make('response.kuesioner.judul')
                            ->label('Kuesioner')
                            ->placeholder('Kuesioner tidak tersedia')
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('response.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'submitted' => 'success',
                                'draft' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('response.penilai.name')
                            ->label('Penilai')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('response.penilai.email')
                            ->label('Email Penilai')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('response.target.nama')
                            ->label('Pegawai Dinilai')
                            ->placeholder('Pegawai tidak tersedia'),
                        Infolists\Components\TextEntry::make('response.target.nip')
                            ->label('NIP Dinilai')
                            ->placeholder('-'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('response.id')
                    ->label('Response ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('response.kuesioner.judul')
                    ->label('Kuesioner')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('response.penilai.name')
                    ->label('Penilai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('response.target.nama')
                    ->label('Dinilai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pertanyaan.urutan')
                    ->label('No Soal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pertanyaan.judul')
                    ->label('Pertanyaan')
                    ->limit(100)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nilai')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 3 => 'danger',
                        $state <= 6 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('response_id')
                    ->label('Response ID')
                    ->relationship('response', 'id')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('pertanyaan_id')
                    ->label('Pertanyaan')
                    ->relationship('pertanyaan', 'judul')
                    ->searchable(),
                Tables\Filters\Filter::make('nilai_rendah')
                    ->label('Nilai 1-3')
                    ->query(fn ($query) => $query->whereBetween('nilai', [1, 3])),
                Tables\Filters\Filter::make('nilai_sedang')
                    ->label('Nilai 4-6')
                    ->query(fn ($query) => $query->whereBetween('nilai', [4, 6])),
                Tables\Filters\Filter::make('nilai_tinggi')
                    ->label('Nilai 7-10')
                    ->query(fn ($query) => $query->whereBetween('nilai', [7, 10])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'pertanyaan',
                'response.kuesioner',
                'response.penilai',
                'response.target',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResponseJawabans::route('/'),
            'view' => Pages\ViewResponseJawaban::route('/{record}'),
        ];
    }
}
