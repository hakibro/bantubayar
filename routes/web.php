<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});






require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/petugas.php';
require __DIR__ . '/penanganan.php';
require __DIR__ . '/custom.php';


Route::prefix('visit')->name('visit.')->group(function () {
    Route::get('/{token}', [VisitController::class, 'form'])->name('form');
    Route::post('/{token}', [VisitController::class, 'submit'])->name('submit');
    Route::get('/thankyou', [VisitController::class, 'thankyou'])->name('thankyou');
});


Route::get('/force-logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('force.logout');

Route::get('/viewfilter', function () {
    return view('petugas.siswa.partials.filter');
});




Route::get('/siswa_tes', [\App\Http\Controllers\Custom\CustomController::class, 'tesSiswa']);
// Route::get('/siswa_tes', function () {
//     try {
//         DB::connection('mysql_second')->getPdo();
//         return "Koneksi Berhasil!";
//     } catch (\Exception $e) {
//         return "Gagal Koneksi: " . $e->getMessage();
//     }
// });