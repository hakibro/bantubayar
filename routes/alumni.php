<?php

use App\Http\Controllers\Alumni\AlumniController;

Route::middleware(['auth', 'role:bendahara|petugas'])->prefix('alumni')->name('alumni.')->group(function () {
    Route::get('', [AlumniController::class, 'index'])->name('index');
    Route::get('/{idperson}', [AlumniController::class, 'show'])->name('show');
});
