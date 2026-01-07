<?php
use App\Http\Controllers\Bendahara\BendaharaController;
use App\Http\Controllers\Bendahara\DashboardController;
use App\Http\Controllers\Admin\SiswaSyncController;


// Group route khusus Bendahara
Route::middleware(['auth', 'role:bendahara'])->prefix('bendahara')->name('bendahara.')->group(function () {

    // Dashboard Bendahara
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/', [BendaharaController::class, 'index'])->name('penanganan.index');
    Route::get('/siswa/{id}', [BendaharaController::class, 'show'])->name('siswa.show');
    Route::post('/siswa/{id}/update', [BendaharaController::class, 'update'])->name('siswa.update');

    // Sync Single Pembayaran Siswa
    Route::get('/siswa/sync-pembayaran-siswa/{id}', [SiswaSyncController::class, 'syncPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');
});