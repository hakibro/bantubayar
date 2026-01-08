<?php
use App\Http\Controllers\Siswa\SiswaController;
use App\Http\Controllers\Petugas\DashboardController;


// Group route khusus Bendahara
Route::middleware(['auth', 'role:bendahara'])->prefix('bendahara')->name('bendahara.')->group(function () {

    // Dashboard Bendahara
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Data Siswa dan Penanganan
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa');
    Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show');
    Route::post('/siswa/{id}/update', [SiswaController::class, 'update'])->name('siswa.update');


});