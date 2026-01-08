<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});







Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/sync/pembayaran', function () {
    \App\Jobs\DispatchSyncPembayaranJob::dispatch();

    return response()->json([
        'status' => true,
        'message' => 'Proses sync pembayaran dimulai.'
    ]);
});

Route::get('/sync/pembayaran/progress', function () {
    $total = Cache::get('sync_total', 0);
    $done = Cache::get('sync_done', 0);

    return [
        'total' => $total,
        'done' => $done,
        'percent' => $total > 0 ? round(($done / $total) * 100, 2) : 0
    ];
});




require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/petugas.php';
require __DIR__ . '/penanganan.php';



Route::get('/force-logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('force.logout');