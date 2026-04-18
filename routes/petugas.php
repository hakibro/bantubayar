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

    // Sync All Pembayaran Summary
    Route::post('/sync-summary-all', [SiswaController::class, 'syncAllSummary'])->name('siswa.sync-summary-all');
    Route::get('/sync-progress/{progressKey}', [SiswaController::class, 'getSyncProgress'])->name('siswa.sync-progress');

    Route::get('/test-sync/{idperson}', [SiswaController::class, 'syncPembayaranSummary'])->name('siswa.sync-pembayaran-summary');

});

// test route
Route::get('/sync-kilat/{id}', function ($id) {
    return app(App\Services\SiswaService::class)->getPembayaranSummary($id);
});

// TEST: Simple job dispatch untuk debugging
Route::get('/debug/dispatch-job', function () {
    try {
        $siswaIds = Siswa::limit(5)->pluck('id')->toArray();
        $progressKey = 'test_dispatch_' . time();

        \Log::info('DEBUG: Dispatching SyncPembayaranSummaryAllJob', [
            'siswa_count' => count($siswaIds),
            'progress_key' => $progressKey
        ]);

        \App\Jobs\SyncPembayaranSummaryAllJob::dispatch($siswaIds, $progressKey);

        // Check apakah job sudah tersimpan
        $jobCount = DB::table('jobs')->count();

        return response()->json([
            'success' => true,
            'message' => 'Job dispatched',
            'progress_key' => $progressKey,
            'siswa_count' => count($siswaIds),
            'total_jobs_in_queue' => $jobCount,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});