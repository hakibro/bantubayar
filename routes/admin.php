<?php
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\AssignController;
use App\Http\Controllers\Admin\SiswaSyncController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\SyncPembayaranController;
use App\Http\Controllers\Admin\PembayaranSiswaController;



// Group route khusus admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard Admin
    Route::get('admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Manajemen Petugas
    Route::get('/petugas', [PenggunaController::class, 'index'])->name('petugas.index');
    Route::get('/petugas/create', [PenggunaController::class, 'create'])->name('petugas.create');
    Route::post('/petugas', [PenggunaController::class, 'store'])->name('petugas.store');
    Route::get('/petugas/{id}/edit', [PenggunaController::class, 'edit'])->name('petugas.edit');
    Route::put('/petugas/{id}', [PenggunaController::class, 'update'])->name('petugas.update');
    Route::delete('/petugas/{id}', [PenggunaController::class, 'destroy'])->name('petugas.destroy');

    // Restore & Permanent Delete
    Route::post('/petugas/{id}/restore', [PenggunaController::class, 'restore'])->name('petugas.restore');
    Route::delete('/petugas/{id}/force', [PenggunaController::class, 'forceDelete'])->name('petugas.forceDelete');

    // Manajemen Siswa
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/kelas', [SiswaController::class, 'kelas'])->name('siswa.kelas');
    Route::get('/siswa/kamar', [SiswaController::class, 'kamar'])->name('siswa.kamar');
    Route::get('/siswa/{id}/details', [SiswaController::class, 'show'])->name('siswa.show');

    // Sync Siswa dari API Eksternal
    Route::get('/siswa/test-api', [SiswaSyncController::class, 'testApi'])->name('siswa.test-api');
    Route::get('/siswa/fetch/{idperson}', [SiswaSyncController::class, 'fetch']);
    Route::get('/siswa/sync/{idperson}', [SiswaSyncController::class, 'sync']);

    Route::get('/siswa/get-all-siswa', [SiswaSyncController::class, 'getAllSiswa']);
    Route::get('/siswa/sync-all-siswa', [SiswaSyncController::class, 'syncAllSiswa'])->name('siswa.sync-data-siswa');
    Route::get('/siswa/sync-pembayaran-siswa/{id}', [SiswaSyncController::class, 'syncPembayaranSiswa'])->name('siswa.sync-pembayaran-siswa');

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
    Route::get('/assign/kamar', [AssignController::class, 'kamar'])->name('assign.kamar');

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
