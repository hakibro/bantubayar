<?php
use App\Http\Controllers\Petugas\SiswaController;
use App\Http\Controllers\Petugas\DashboardController;
use App\Http\Controllers\Admin\SiswaSyncController;
use App\Models\Siswa;



// Group route khusus Bendahara dan Petugas
Route::middleware(['auth', 'role:bendahara|petugas'])->prefix('petugas')->name('petugas.')->group(function () {

    // Dashboard Petugas
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // -------------------------------------------- Route Lama --------------------------------------------
    // Data Siswa dan Penanganan
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
    Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show');
    Route::post('/siswa/{id}/update', [SiswaController::class, 'update'])->name('siswa.update');

    // Sync Single Pembayaran Siswa
    Route::get('/siswa/sync-pembayaran-siswa/{id}', [SiswaSyncController::class, 'syncPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');

    Route::get('/test-sync/{idperson}', [SiswaController::class, 'syncPembayaranSummary'])->name('siswa.sync-pembayaran-summary');

});

// test route
Route::get('/sync-kilat/{id}', function ($id) {
    return app(App\Services\SiswaService::class)->getPembayaranSummary($id);
});