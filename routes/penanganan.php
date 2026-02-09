<?php

use App\Http\Controllers\Penanganan\PenangananController;


Route::get('penanganan/show/{id_siswa}', [PenangananController::class, 'show'])
    ->name('penanganan.show');

Route::middleware(['auth:web', 'role:bendahara|petugas'])->prefix('penanganan')->name('penanganan.')->group(function () {

    Route::get('', [PenangananController::class, 'index'])->name('index');


    Route::post('/store', [PenangananController::class, 'store'])->name('store');
    Route::post('/save-hasil', [PenangananController::class, 'saveHasil'])->name('save_hasil');
    Route::post('/update-phone', [PenangananController::class, 'updatePhone'])->name('update_phone');
    Route::post('/kesanggupan', [PenangananController::class, 'kirimKesanggupan'])->name('kesanggupan');

    Route::put('/update/{penanganan}', [PenangananController::class, 'update'])
        ->name('update');

});

Route::prefix('wali')->name('wali.')->group(function () {
    Route::get(
        '/kesanggupan/{token}',
        [PenangananController::class, 'formKesanggupan']
    )->name('kesanggupan.form');
    Route::post(
        '/kesanggupan/{token}',
        [PenangananController::class, 'submitKesanggupan']
    )->name('kesanggupan.submit');
});


// test api update telepon

Route::get('/test-api-phone', function () {
    return view('penanganan.apiPhone');
});


Route::post('/test-api-phone', [App\Http\Controllers\ApiPhoneController::class, 'update'])
    ->name('api.phone');
