<?php

use App\Http\Controllers\Custom\CustomController;



Route::prefix('custom')->group(function () {
    Route::get('/belum-lunas', [CustomController::class, 'index'])->name('siswa.belum-lunas.index');
    Route::get('/belum-lunas/export', [CustomController::class, 'export'])->name('siswa.belum-lunas.export');
});