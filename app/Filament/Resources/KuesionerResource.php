<?php

// Resource Filament untuk mengelola periode kuesioner dan pertanyaan.

namespace App\Filament\Resources;

use App\Filament\Resources\KuesionerResource\Pages;
use App\Models\Kuesioner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KuesionerResource extends Resource
{
    protected static ?string $model = Kuesioner::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Kuesioner 360';

    protected static ?string $modelLabel = 'Kuesioner';

    protected static ?string $pluralModelLabel = 'Kuesioner';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('TW1-2025')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('triwulan')
                            ->options(self::triwulanOptions())
                            ->required(),
                        Forms\Components\TextInput::make('tahun')
                            ->numeric()
                            ->required()
                            ->default(now()->year)
                            ->minValue(2020)
                            ->maxValue(2100),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('dibuka_pada')
                            ->nullable(),
                        Forms\Components\DatePicker::make('ditutup_pada')
                            ->nullable(),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'closed' => 'Closed',
                            ])
                            ->default('active')
                            ->required(),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('assign_all')
                            ->label('Assign ke Semua Pegawai')
                            ->default(true)
                            ->reactive()
                            ->required()
                            ->inline(false),
                        Forms\Components\Select::make('excluded_pegawai_ids')
                            ->label('Kecualikan Pegawai')
                            ->options(fn () => \App\Models\Pegawai::query()->active()->orderBy('nama')->pluck('nama', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih pegawai yang dikecualikan dari kuesioner ini')
                            ->visible(fn (Forms\Get $get) => $get('assign_all') ?? true)
                            ->helperText(function (?Kuesioner $record) {
                                if ($record && $record->status === 'closed' && $record->responses()->exists()) {
                                    return 'Data otomatis dari excel yang diimportkan';
                                }
                                return null;
                            }),
                    ]),

                Forms\Components\Textarea::make('deskripsi')
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\Section::make('Kelola Pertanyaan Kuesioner')
                    ->description('Pilih pertanyaan yang akan aktif dalam kuesioner ini. Gunakan "Pilih Semua" (Ceklis All) untuk memilih semua secara massal, atau buat pertanyaan baru langsung dari sini.')
                    ->collapsible()
                    ->collapsed()
                    ->headerActions([
                        Forms\Components\Actions\Action::make('create_pertanyaan')
                            ->label('Buat Pertanyaan Baru')
                            ->icon('heroicon-m-plus')
                            ->form([
                                Forms\Components\TextInput::make('judul')
                                    ->label('Judul Pertanyaan')
                                    ->required(),
                                Forms\Components\Textarea::make('isi')
                                    ->label('Isi Pertanyaan')
                                    ->required()
                                    ->rows(3),
                                Forms\Components\TextInput::make('urutan')
                                    ->numeric()
                                    ->required()
                                    ->default(fn () => (\App\Models\Pertanyaan::max('urutan') ?? 0) + 1),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('poin_min')
                                            ->label('Poin Minimal')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),
                                        Forms\Components\TextInput::make('poin_max')
                                            ->label('Poin Maksimal')
                                            ->numeric()
                                            ->default(10)
                                            ->required(),
                                    ]),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Pertanyaan Aktif')
                                    ->default(true),
                            ])
                            ->action(function (array $data, Forms\Set $set, Forms\Get $get) {
                                $pertanyaan = \App\Models\Pertanyaan::create($data);
                                
                                $currentSelected = $get('pertanyaans') ?? [];
                                $currentSelected[] = $pertanyaan->id;
                                
                                $set('pertanyaans', $currentSelected);
                            })
                    ])
                    ->schema([
                        Forms\Components\CheckboxList::make('pertanyaans')
                            ->relationship(
                                name: 'pertanyaans',
                                titleAttribute: 'judul',
                                modifyQueryUsing: fn ($query) => $query->orderBy('urutan')
                            )
                            ->label('Daftar Pertanyaan')
                            ->columns(1)
                            ->bulkToggleable()
                            ->searchable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('triwulan')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => self::triwulanOptions()[$state] ?? 'TW tidak valid')
                    ->color('info'),
                Tables\Columns\TextColumn::make('tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'closed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('dibuka_pada')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ditutup_pada')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pertanyaans_count')
                    ->counts('pertanyaans')
                    ->label('Jumlah Soal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('responses_count')
                    ->counts('responses')
                    ->label('Jumlah Respon')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('triwulan')
                    ->options(self::triwulanOptions()),
                Tables\Filters\SelectFilter::make('tahun')
                    ->options(fn (): array => Kuesioner::query()
                        ->distinct()
                        ->orderByDesc('tahun')
                        ->pluck('tahun', 'tahun')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Kuesioner $record): string => route('kuesioner.export', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKuesioners::route('/'),
            'create' => Pages\CreateKuesioner::route('/create'),
            'edit' => Pages\EditKuesioner::route('/{record}/edit'),
        ];
    }

    /**
     * Daftar opsi triwulan untuk form dan table.
     *
     * @return array<int, string>
     */
    public static function triwulanOptions(): array
    {
        return [
            1 => 'TW1 (Jan-Mar)',
            2 => 'TW2 (Apr-Jun)',
            3 => 'TW3 (Jul-Sep)',
            4 => 'TW4 (Okt-Des)',
        ];
    }
}
