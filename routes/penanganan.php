<?php

use App\Http\Controllers\Penanganan\PenangananController;


Route::middleware(['auth:web', 'role:bendahara|petugas'])->prefix('penanganan')->name('penanganan.')->group(function () {

    Route::get('', [PenangananController::class, 'index'])->name('index');
    Route::get('/show/{id_siswa}', [PenangananController::class, 'show'])
        ->name('show');

    Route::get('/create/{penanganan}', [PenangananController::class, 'create'])->name('create');
    Route::post('/store', [PenangananController::class, 'store'])->name('store');
    Route::get('/edit/{penanganan}', [PenangananController::class, 'edit'])
        ->name('edit');

    Route::put('/update/{penanganan}', [PenangananController::class, 'update'])
        ->name('update');

});