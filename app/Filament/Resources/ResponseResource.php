<?php

// Resource Filament untuk monitoring response kuesioner yang masuk.

namespace App\Filament\Resources;

use App\Filament\Resources\ResponseResource\Pages;
use App\Models\Response as KuesionerResponse;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResponseResource extends Resource
{
    protected static ?string $model = KuesionerResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Kuesioner 360';

    protected static ?string $modelLabel = 'Response';

    protected static ?string $pluralModelLabel = 'Response';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ringkasan Response')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Response ID'),
                        Infolists\Components\TextEntry::make('kuesioner.judul')
                            ->label('Kuesioner')
                            ->placeholder('Kuesioner tidak tersedia')
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'submitted' => 'success',
                                'draft' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('progress')
                            ->label('Progress Jawaban')
                            ->formatStateUsing(fn (int $state): string => "{$state}%")
                            ->badge()
                            ->color(fn (int $state): string => $state >= 100 ? 'success' : 'warning'),
                        Infolists\Components\TextEntry::make('submitted_at')
                            ->label('Dikirim Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum dikirim final'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diubah')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ]),

                Infolists\Components\Section::make('Penilai dan Pegawai Dinilai')
                    ->schema([
                        Infolists\Components\TextEntry::make('penilai.name')
                            ->label('Nama Penilai')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('penilai.email')
                            ->label('Email Penilai')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('target.nama')
                            ->label('Pegawai Dinilai')
                            ->placeholder('Pegawai tidak tersedia'),
                        Infolists\Components\TextEntry::make('target.nip')
                            ->label('NIP Dinilai')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('target.jabatan')
                            ->label('Jabatan Dinilai')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('target.departemen')
                            ->label('Unit Dinilai')
                            ->placeholder('-'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),

                Infolists\Components\Section::make('Jawaban Per Soal')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('jawabans')
                            ->hiddenLabel()
                            ->getStateUsing(fn (KuesionerResponse $record) => $record->jawabans
                                ->sortBy(fn ($jawaban) => $jawaban->pertanyaan?->urutan ?? PHP_INT_MAX)
                                ->values())
                            ->schema([
                                Infolists\Components\TextEntry::make('pertanyaan.urutan')
                                    ->label('No Soal')
                                    ->badge()
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('nilai')
                                    ->badge()
                                    ->color(fn (int $state): string => match (true) {
                                        $state <= 3 => 'danger',
                                        $state <= 6 => 'warning',
                                        default => 'success',
                                    }),
                                Infolists\Components\TextEntry::make('pertanyaan.teks')
                                    ->label('Pertanyaan')
                                    ->placeholder('Pertanyaan tidak tersedia')
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),
                            ])
                            ->grid([
                                'default' => 1,
                                'md' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kuesioner.judul')
                    ->label('Kuesioner')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('penilai.name')
                    ->label('Penilai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('penilai.email')
                    ->label('Email Penilai')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('target.nama')
                    ->label('Dinilai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('target.nip')
                    ->label('NIP Dinilai')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('jawabans_count')
                    ->counts('jawabans')
                    ->label('Soal Dijawab')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kuesioner_id')
                    ->label('Kuesioner')
                    ->relationship('kuesioner', 'judul')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                    ]),
                Tables\Filters\SelectFilter::make('penilai_id')
                    ->label('Penilai')
                    ->relationship('penilai', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'kuesioner',
                'penilai',
                'target',
            ])
            ->withCount('jawabans');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResponses::route('/'),
            'view' => Pages\ViewResponse::route('/{record}'),
        ];
    }
}
