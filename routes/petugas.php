<?php
use App\Http\Controllers\Petugas\SiswaController;
use App\Http\Controllers\Petugas\DashboardController;
use App\Http\Controllers\Admin\SiswaSyncController;
use App\Services\SiswaService;
use App\Models\Siswa;



// Group route khusus Bendahara dan Petugas
Route::middleware(['auth', 'role:bendahara|petugas'])->prefix('petugas')->name('petugas.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ Route statis/spesifik HARUS di atas route wildcard {id}
    Route::post('/siswa/sync-summary-all', [SiswaController::class, 'syncAllSummary'])->name('siswa.sync-summary-all');
    Route::post('/siswa/sync-summary-cancel', [SiswaController::class, 'cancelSyncSummary'])->name('siswa.sync-summary-cancel');
    Route::get('/siswa/sync-summary-active-batch', [SiswaController::class, 'getActiveBatch'])->name('siswa.sync-summary-active-batch');
    Route::get('/siswa/sync-summary-progress/{batchId}', [SiswaController::class, 'getSyncSummaryProgress'])->name('siswa.sync-summary-progress');
    Route::get('/siswa/sync-pembayaran-siswa/{id}', [SiswaSyncController::class, 'syncKategoriPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');

    // ✅ Route wildcard {id} di BAWAH
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
    Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show');
    Route::post('/siswa/{id}/update', [SiswaController::class, 'update'])->name('siswa.update');
});


// cek siswaservice fungsi syncpembayaransummarysiswa(idperson)
Route::get('/test-summary/{idperson}', function (SiswaService $siswaService, $idperson) {
    $result = $siswaService->syncPembayaranSummarySiswa($idperson);

    dd($result);
});
