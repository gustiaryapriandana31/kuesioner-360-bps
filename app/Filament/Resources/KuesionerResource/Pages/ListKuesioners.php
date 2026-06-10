<?php

// Halaman daftar kuesioner dengan aksi membuat dan copy triwulan.

namespace App\Filament\Resources\KuesionerResource\Pages;

use App\Filament\Resources\KuesionerResource;
use App\Models\Kuesioner;
use App\Services\KuesionerService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\ValidationException;

class ListKuesioners extends ListRecords
{
    protected static string $resource = KuesionerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import_excel')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('kuesioner_id')
                        ->label('Kuesioner Tujuan')
                        ->options(fn () => Kuesioner::query()->orderByDesc('tahun')->orderBy('triwulan')->get()->mapWithKeys(fn ($k) => [$k->id => "[{$k->kode}] {$k->judul}"]))
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('agree_replace', false)),

                    Forms\Components\Placeholder::make('warning_message')
                        ->label('⚠️ Peringatan!')
                        ->content(function (Forms\Get $get) {
                            $kuesionerId = $get('kuesioner_id');
                            if (! $kuesionerId) return '';
                            $kuesioner = Kuesioner::find($kuesionerId);
                            if (! $kuesioner) return '';
                            $count = $kuesioner->responses()->count();
                            if ($count === 0) return '';
                            return new \Illuminate\Support\HtmlString(
                                "<div class='text-amber-600 font-bold p-3 border border-amber-300 rounded bg-amber-50 dark:bg-amber-950/40 dark:border-amber-800'>
                                    Kuesioner ini sudah memiliki {$count} data respon!
                                    Melakukan import baru akan menghapus seluruh data respon lama kuesioner ini beserta seluruh detail jawabannya secara permanen dan mereplace dengan data baru.
                                </div>"
                            );
                        })
                        ->visible(function (Forms\Get $get) {
                            $kuesionerId = $get('kuesioner_id');
                            if (! $kuesionerId) return false;
                            $kuesioner = Kuesioner::find($kuesionerId);
                            return $kuesioner && $kuesioner->responses()->exists();
                        }),

                    Forms\Components\Checkbox::make('agree_replace')
                        ->label('Saya mengerti dan menyetujui untuk menghapus seluruh data respon lama kuesioner ini.')
                        ->live()
                        ->visible(function (Forms\Get $get) {
                            $kuesionerId = $get('kuesioner_id');
                            if (! $kuesionerId) return false;
                            $kuesioner = Kuesioner::find($kuesionerId);
                            return $kuesioner && $kuesioner->responses()->exists();
                        }),

                    Forms\Components\FileUpload::make('file')
                        ->label('File Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->disk('local')
                        ->directory('imports/temp')
                        ->maxSize(20480)
                        ->required()
                        ->live(),
                ])
                ->action(function (array $data): void {
                    $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($data['file']);
                    
                    $kuesioner = Kuesioner::query()->findOrFail((int) $data['kuesioner_id']);
                    $status = $kuesioner->status === 'closed' ? 'submitted' : 'draft';
                    $deleteExisting = !empty($data['agree_replace']);

                    $service = new \App\Services\ImportResponseService();
                    $result = $service->import(
                        $absolutePath,
                        (int) $data['kuesioner_id'],
                        $status,
                        auth()->id(),
                        $deleteExisting
                    );

                    if ($result['success']) {
                        $stats = $result['stats'];
                        $msg = "Diproses: {$stats['total_penilai']} Penilai | Response: {$stats['total_response']} | Jawaban: {$stats['total_jawaban']}";
                        
                        Notification::make()
                            ->title('Import Excel Berhasil')
                            ->body($msg)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Import Response Excel')
                ->modalDescription('Upload file hasil dari Google Forms. Mohon tunggu sesaat setelah klik import.')
                ->modalSubmitActionLabel('Mulai Import')
                ->modalSubmitAction(function (\Filament\Actions\StaticAction $action, Forms\Get $get) {
                    $kuesionerId = $get('kuesioner_id');
                    if (! $kuesionerId || empty($get('file'))) {
                        return $action->disabled(true);
                    }
                    $kuesioner = Kuesioner::find($kuesionerId);
                    if (! $kuesioner) {
                        return $action->disabled(true);
                    }
                    $hasResponses = $kuesioner->responses()->exists();
                    if ($hasResponses && ! $get('agree_replace')) {
                        return $action->disabled(true);
                    }
                    return $action->disabled(false);
                }), 
            Actions\Action::make('copy_tw')
                ->label('Copy Kuesioner')
                ->icon('heroicon-o-document-duplicate')
                ->form([
                    Forms\Components\Select::make('source_id')
                        ->label('Kuesioner sumber')
                        ->options(fn (): array => Kuesioner::query()
                            ->orderByDesc('tahun')
                            ->orderByDesc('triwulan')
                            ->pluck('judul', 'id')
                            ->toArray())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        $source = Kuesioner::query()->findOrFail($data['source_id']);
                        $copy = app(KuesionerService::class)->copyFromTW($source);

                        Notification::make()
                            ->title('Kuesioner berhasil dicopy')
                            ->body("Kuesioner {$copy->kode} dibuat sebagai draft.")
                            ->success()
                            ->send();
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Copy Kuesioner gagal')
                            ->body(collect($exception->errors())->flatten()->first())
                            ->danger()
                            ->send();
                    }
                })
                ->modalSubmitActionLabel('Copy'),
        ];
    }
}
