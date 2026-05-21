<?php

use App\Http\Controllers\Petugas\DashboardController;
use App\Http\Controllers\Petugas\SiswaController;

Route::middleware(['auth', 'role:bendahara|petugas'])->prefix('petugas')->name('petugas.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/siswa/export', [DashboardController::class, 'exportSiswa'])->name('dashboard.siswa.export');

    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
    Route::get('/siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show');
});
