<?php

use App\Http\Controllers\Api\PenangananController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('api.admin.')
    ->group(function () {
        Route::get('/penanganan', [PenangananController::class, 'index'])->name('penanganan.index');
    });
