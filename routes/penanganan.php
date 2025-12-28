<?php

use App\Http\Controllers\Penanganan\PenangananController;


Route::middleware(['auth:web', 'role:bendahara|petugas'])->prefix('penanganan')->name('penanganan.')->group(function () {

    Route::get('', [PenangananController::class, 'index'])->name('index');
    Route::get('/siswa/{id_siswa}', [PenangananController::class, 'indexSiswa'])
        ->name('siswa');

    Route::get('/create/{siswa}', [PenangananController::class, 'create'])->name('create');
    Route::post('/store', [PenangananController::class, 'store'])->name('store');
});