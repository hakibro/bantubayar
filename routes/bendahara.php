<?php
use App\Http\Controllers\Bendahara\BendaharaController;
use App\Http\Controllers\Admin\SiswaSyncController;


// Group route khusus Bendahara
Route::middleware(['auth', 'role:bendahara'])->prefix('bendahara')->name('bendahara.')->group(function () {

    // Dashboard Bendahara
    Route::get('bendahara/dashboard', function () {
        return view('bendahara.dashboard');
    })->name('dashboard');

    // Penanganan (misalnya: menangani siswa, laporan, atau pembayaran)
    Route::get('/penanganan', [BendaharaController::class, 'index'])->name('penanganan.index');
    Route::get('/penanganan/{id}', [BendaharaController::class, 'show'])->name('penanganan.show');
    Route::post('/penanganan/{id}/update', [BendaharaController::class, 'update'])->name('penanganan.update');
    Route::get('/siswa/{id}', [BendaharaController::class, 'show'])->name('siswa.show');
    Route::get('/siswa/sync-pembayaran-siswa/{id}', [SiswaSyncController::class, 'syncPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');

});