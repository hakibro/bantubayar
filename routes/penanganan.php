<?php

use App\Http\Controllers\Penanganan\PenangananController;


Route::middleware(['auth:web', 'role:bendahara|petugas'])->prefix('penanganan')->name('penanganan.')->group(function () {

    Route::get('', [PenangananController::class, 'index'])->name('index');
    Route::get('/show/{id_siswa}', [PenangananController::class, 'show'])
        ->name('show');

    Route::post('/store', [PenangananController::class, 'store'])->name('store');
    Route::post('/save-hasil', [PenangananController::class, 'saveHasil'])->name('save_hasil');
    Route::post('/update-phone', [PenangananController::class, 'updatePhone'])->name('update_phone');

    Route::put('/update/{penanganan}', [PenangananController::class, 'update'])
        ->name('update');

});