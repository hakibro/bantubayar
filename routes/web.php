<?php

use App\Http\Controllers\Admin\PetugasController;
use App\Http\Controllers\Admin\PembayaranSiswaController;
use App\Http\Controllers\Admin\AssignController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\SyncPembayaranController;
use App\Http\Controllers\Petugas\PenangananController;
use App\Http\Controllers\Admin\SiswaSyncController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Group route khusus admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Manajemen Petugas
    Route::get('/petugas', [PetugasController::class, 'index'])->name('petugas.index');
    Route::get('/petugas/create', [PetugasController::class, 'create'])->name('petugas.create');
    Route::post('/petugas', [PetugasController::class, 'store'])->name('petugas.store');
    Route::get('/petugas/{id}/edit', [PetugasController::class, 'edit'])->name('petugas.edit');
    Route::put('/petugas/{id}', [PetugasController::class, 'update'])->name('petugas.update');
    Route::delete('/petugas/{id}', [PetugasController::class, 'destroy'])->name('petugas.destroy');
    // Restore & Permanent Delete
    Route::post('/petugas/{id}/restore', [PetugasController::class, 'restore'])->name('petugas.restore');
    Route::delete('/petugas/{id}/force', [PetugasController::class, 'forceDelete'])->name('petugas.forceDelete');


    // Manajemen Siswa
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    // Sync Siswa dari API Eksternal
    Route::get('/siswa/test-api', [SiswaSyncController::class, 'testApi']);
    Route::get('/siswa/fetch/{idperson}', [SiswaSyncController::class, 'fetch']);
    Route::get('/siswa/sync/{idperson}', [SiswaSyncController::class, 'sync']);

    Route::get('/siswa/get-all-siswa', [SiswaSyncController::class, 'getAllSiswa']);
    Route::get('/siswa/sync-all-siswa', [SiswaSyncController::class, 'syncAllSiswa'])->name('siswa.sync-data-siswa');
    Route::get('/siswa/sync-pembayaran-siswa', [SiswaSyncController::class, 'syncPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');
    Route::get('/siswa/get-progress-pembayaran', function () {
        $total = cache()->get('sync_pembayaran_total', 1);
        $processed = cache()->get('sync_pembayaran_processed', 0);

        $percent = floor(($processed / $total) * 100);

        return response()->json([
            'progress' => $percent
        ]);
    });

    // Sinkronisasi Pembayaran (Halaman & API)
    Route::get('/sync-pembayaran', [SyncPembayaranController::class, 'index'])->name('sync-pembayaran.index');
    Route::post('/sync-pembayaran/start', [SyncPembayaranController::class, 'start'])->name('sync-pembayaran.start');
    Route::post('/sync-pembayaran/cancel', [SyncPembayaranController::class, 'cancel'])->name('sync-pembayaran.cancel');
    Route::get('/sync-pembayaran/progress', [SyncPembayaranController::class, 'progress'])->name('sync-pembayaran.progress');
    Route::post('/sync-pembayaran/reset', [SyncPembayaranController::class, 'reset'])->name('sync-pembayaran.reset');

    Route::get('/siswa/get-pembayaran-siswa/{idperson}', [SiswaSyncController::class, 'getPembayaranSiswa'])->name('siswa.get-pembayaran-siswa');

    // Assign Siswa ke Petugas
    Route::get('/assign', [AssignController::class, 'index'])->name('assign.index');

    // Optional: endpoint to get kelas (per lembaga) jika butuh
    Route::get('/assign/kelas', [AssignController::class, 'kelas'])->name('assign.kelas');

    Route::post('/assign/siswa', [AssignController::class, 'assign'])->name('assign.siswa');
    Route::post('/assign/unassign', [AssignController::class, 'unassign'])->name('assign.unassign');
    Route::post('/assign/store', [AssignController::class, 'store'])->name('assign.store');
    Route::delete('/assign/{id}', [AssignController::class, 'destroy'])->name('assign.destroy');

    // Bulk Assign & Unassign
    Route::post('/assign/bulk', [AssignController::class, 'bulk'])->name('assign.bulk');
    Route::post('/assign/bulk-unassign', [AssignController::class, 'bulkUnassign'])
        ->name('assign.bulkUnassign');

    // Pembayaran Siswa
    Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index'])->name('pembayaran-siswa.index');


});

// Group route khusus Petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {

    // Dashboard Petugas
    Route::get('/dashboard', function () {
        return view('petugas.dashboard');
    })->name('dashboard');

    // Penanganan (misalnya: menangani siswa, laporan, atau pembayaran)
    Route::get('/penanganan', [PenangananController::class, 'index'])->name('penanganan.index');
    Route::get('/penanganan/{id}', [PenangananController::class, 'show'])->name('penanganan.show');
    Route::post('/penanganan/{id}/update', [PenangananController::class, 'update'])->name('penanganan.update');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/sync/pembayaran', function () {
    \App\Jobs\DispatchSyncPembayaranJob::dispatch();

    return response()->json([
        'status' => true,
        'message' => 'Proses sync pembayaran dimulai.'
    ]);
});

Route::get('/sync/pembayaran/progress', function () {
    $total = Cache::get('sync_total', 0);
    $done = Cache::get('sync_done', 0);

    return [
        'total' => $total,
        'done' => $done,
        'percent' => $total > 0 ? round(($done / $total) * 100, 2) : 0
    ];
});




require __DIR__ . '/auth.php';
