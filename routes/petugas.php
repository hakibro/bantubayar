<?php
use App\Http\Controllers\Petugas\SiswaController;
use App\Http\Controllers\Petugas\DashboardController;
use App\Http\Controllers\Admin\SiswaSyncController;
use App\Services\SiswaService;
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
    Route::get('/siswa/sync-pembayaran-siswa/{id}', [SiswaSyncController::class, 'syncKategoriPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');

    // Sync All Pembayaran Summary
    Route::post('/sync-summary-all', [SiswaController::class, 'syncAllSummary'])->name('siswa.sync-summary-all');
    Route::get('/sync-progress/{progressKey}', [SiswaController::class, 'getSyncProgress'])->name('siswa.sync-progress');
    Route::post('/sync-summary-cancel', [SiswaController::class, 'cancelSyncSummary'])->name('siswa.sync-summary-cancel');
    Route::get('/test-sync/{idperson}', [SiswaController::class, 'syncPembayaranSummary'])->name('siswa.sync-pembayaran-summary');

});


// cek siswaservice fungsi syncpembayaransummarysiswa(idperson)
Route::get('/test-summary/{idperson}', function (SiswaService $siswaService, $idperson) {
    $result = $siswaService->syncPembayaranSummarySiswa($idperson);

    dd($result);
});
