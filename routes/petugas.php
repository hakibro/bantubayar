<?php
use App\Http\Controllers\Petugas\PenangananController;

// Group route khusus Petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {

    // Dashboard Petugas
    Route::get('petugas/dashboard', function () {
        return view('petugas.dashboard');
    })->name('dashboard');

    // Penanganan (misalnya: menangani siswa, laporan, atau pembayaran)
    Route::get('/penanganan', [PenangananController::class, 'index'])->name('penanganan.index');
    Route::get('/penanganan/{id}', [PenangananController::class, 'show'])->name('penanganan.show');
    Route::post('/penanganan/{id}/update', [PenangananController::class, 'update'])->name('penanganan.update');
});