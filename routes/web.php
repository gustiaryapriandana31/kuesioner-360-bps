<?php

// Route web untuk welcome page, halaman kuesioner Inertia, API response, dan import Excel.

use App\Http\Controllers\Auth\PegawaiLoginController;
use App\Http\Controllers\Import\ImportResponseController;
use App\Http\Controllers\Kuesioner\KuesionerController;
use App\Http\Controllers\Kuesioner\ResponseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/masuk');
Route::get('/masuk', [PegawaiLoginController::class, 'create'])->name('login');
Route::post('/masuk', [PegawaiLoginController::class, 'store'])->name('pegawai.login.store');
Route::post('/keluar', [PegawaiLoginController::class, 'destroy'])->middleware('auth')->name('pegawai.logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/kuesioner/{kuesioner}/export', [KuesionerController::class, 'export'])->name('kuesioner.export');
});

Route::middleware(['auth', 'pegawai'])->group(function () {
    Route::get('/kuesioner', [KuesionerController::class, 'index'])->name('kuesioner.index');
    Route::get('/kuesioner/{kode}/{pegawai_nama?}', [KuesionerController::class, 'show'])->name('kuesioner.show');
    Route::post('/kuesioner/{kuesioner}/submit-all', [KuesionerController::class, 'submitAll'])->name('kuesioner.submit-all');

    Route::post('/api/responses', [ResponseController::class, 'store']);
    Route::post('/api/responses/quick-submit', [ResponseController::class, 'quickSubmit']);
    Route::get('/api/responses/answers', [ResponseController::class, 'savedAnswers']);
    Route::patch('/api/responses/answer', [ResponseController::class, 'updateSingleAnswer']);
    Route::put('/api/responses/{response}', [ResponseController::class, 'update']);
    Route::get('/api/kuesioner/{kuesioner}/progress', [ResponseController::class, 'getProgress']);
});
