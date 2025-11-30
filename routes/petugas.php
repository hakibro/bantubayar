<?php
use App\Http\Controllers\Petugas\PetugasController;

// Group route khusus Petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {

    // Dashboard Petugas
    Route::get('petugas/dashboard', function () {
        return view('petugas.dashboard');
    })->name('dashboard');

    // Penanganan (misalnya: menangani siswa, laporan, atau pembayaran)
    Route::get('/petugas', [PetugasController::class, 'index'])->name('petugas.index');
    Route::get('/petugas/{id}', [PetugasController::class, 'show'])->name('petugas.show');
    Route::post('/petugas/{id}/update', [PetugasController::class, 'update'])->name('petugas.update');
});