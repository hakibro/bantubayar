<?php

use App\Http\Controllers\Admin\PetugasController;
use App\Http\Controllers\Admin\AssignController;
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


    // Sync Siswa dari API Eksternal
    Route::get('/siswa/test-api', [SiswaSyncController::class, 'testApi']);
    Route::get('/siswa/fetch/{idperson}', [SiswaSyncController::class, 'fetch']);
    Route::get('/siswa/sync/{idperson}', [SiswaSyncController::class, 'sync']);

    Route::get('/siswa/sync-all', [SiswaSyncController::class, 'index']);
    Route::get('/siswa/sync-all/run', [SiswaSyncController::class, 'getAllSiswa']);

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



    // Route::post('/assign', [AssignController::class, 'store'])->name('assign.store');
    // Route::delete('/assign/{id}', [AssignController::class, 'destroy'])->name('assign.destroy');

    // Auto Pesan WhatsApp
    // Route::get('/whatsapp', [App\Http\Controllers\Admin\WhatsappController::class, 'index'])->name('whatsapp.index');
    // Route::post('/whatsapp/send', [App\Http\Controllers\Admin\WhatsappController::class, 'send'])->name('whatsapp.send');
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

require __DIR__ . '/auth.php';
