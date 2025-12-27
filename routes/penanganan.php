<?php

use App\Http\Controllers\Penanganan\PenangananController;

Route::get('/penanganan', [PenangananController::class, 'index'])->name('penanganan.index');
Route::get('/penanganan/siswa/{id_siswa}', [PenangananController::class, 'indexSiswa'])
    ->name('penanganan.siswa');

Route::get('/penanganan/create/{siswa}', [PenangananController::class, 'create'])->name('penanganan.create');
Route::post('/penanganan/store', [PenangananController::class, 'store'])->name('penanganan.store');
