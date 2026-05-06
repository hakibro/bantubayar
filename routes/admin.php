<?php

use App\Http\Controllers\Admin\AssignController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeVisitController;
use App\Http\Controllers\Admin\LaporanPetugasController;
use App\Http\Controllers\Admin\PembayaranSiswaController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\SiswaController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Petugas
    Route::get('/petugas', [PenggunaController::class, 'index'])->name('petugas.index');
    Route::get('/petugas/create', [PenggunaController::class, 'create'])->name('petugas.create');
    Route::post('/petugas', [PenggunaController::class, 'store'])->name('petugas.store');
    Route::get('/petugas/{id}/edit', [PenggunaController::class, 'edit'])->name('petugas.edit');
    Route::put('/petugas/{id}', [PenggunaController::class, 'update'])->name('petugas.update');
    Route::delete('/petugas/{id}', [PenggunaController::class, 'destroy'])->name('petugas.destroy');
    Route::post('/petugas/{id}/restore', [PenggunaController::class, 'restore'])->name('petugas.restore');
    Route::delete('/petugas/{id}/force', [PenggunaController::class, 'forceDelete'])->name('petugas.forceDelete');

    // Manajemen Siswa
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/kelas', [SiswaController::class, 'kelas'])->name('siswa.kelas');
    Route::get('/siswa/kamar', [SiswaController::class, 'kamar'])->name('siswa.kamar');
    Route::get('/siswa/kelasDiniyah', [SiswaController::class, 'kelasDiniyah'])->name('siswa.kelasDiniyah');
    Route::get('/siswa/{id}/details', [SiswaController::class, 'show'])->name('siswa.show');

    // Assign Siswa ke Petugas
    Route::get('/assign', [AssignController::class, 'index'])->name('assign.index');
    Route::get('/assign/kelas', [AssignController::class, 'kelas'])->name('assign.kelas');
    Route::get('/assign/kamar', [AssignController::class, 'kamar'])->name('assign.kamar');
    Route::post('/assign/siswa', [AssignController::class, 'assign'])->name('assign.siswa');
    Route::post('/assign/unassign', [AssignController::class, 'unassign'])->name('assign.unassign');
    Route::post('/assign/store', [AssignController::class, 'store'])->name('assign.store');
    Route::post('/assign/bulk', [AssignController::class, 'bulk'])->name('assign.bulk');
    Route::post('/assign/bulk-unassign', [AssignController::class, 'bulkUnassign'])->name('assign.bulkUnassign');

    // Pembayaran Siswa
    Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index'])->name('pembayaran-siswa.index');

    // Home Visit
    Route::prefix('home-visit')->name('home-visit.')->group(function () {
        Route::get('/select', [HomeVisitController::class, 'select'])->name('select');
        Route::get('/kelas', [HomeVisitController::class, 'kelas'])->name('kelas');
        Route::get('/kamar', [HomeVisitController::class, 'kamar'])->name('kamar');
        Route::get('/create', [HomeVisitController::class, 'create'])->name('create');
        Route::post('/', [HomeVisitController::class, 'store'])->name('store');
        Route::get('/{id}', [HomeVisitController::class, 'show'])->name('show');
        Route::get('/{id}/cetak', [HomeVisitController::class, 'cetak'])->name('cetak');
    });

    Route::get('/laporan/petugas', [LaporanPetugasController::class, 'index'])->name('laporan.petugas');
});
