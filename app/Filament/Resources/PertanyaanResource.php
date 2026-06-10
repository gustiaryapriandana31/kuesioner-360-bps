<?php

// Resource Filament untuk menampilkan dan mengelola seluruh data pertanyaan kuesioner.

namespace App\Filament\Resources;

use App\Filament\Resources\PertanyaanResource\Pages;
use App\Models\Pertanyaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PertanyaanResource extends Resource
{
    protected static ?string $model = Pertanyaan::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Kuesioner 360';

    protected static ?string $modelLabel = 'Pertanyaan';

    protected static ?string $pluralModelLabel = 'Pertanyaan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('isi')
                    ->required()
                    ->rows(5)
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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('judul')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('isi')
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('poin_min')
                    ->label('Poin Min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('poin_max')
                    ->label('Poin Max')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPertanyaans::route('/'),
            'create' => Pages\CreatePertanyaan::route('/create'),
            'view' => Pages\ViewPertanyaan::route('/{record}'),
            'edit' => Pages\EditPertanyaan::route('/{record}/edit'),
        ];
    }
}
