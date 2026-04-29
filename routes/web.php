<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SyncPembayaranSummarySiswaJob;
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


// test job sync pembayaran summary siswa


// 1. Test Trigger Batch
Route::get('/debug-start-batch', function () {
    // Ambil 5 ID siswa saja untuk tes
    $siswaIds = \App\Models\Siswa::limit(5)->pluck('id')->toArray();

    $jobs = [];
    foreach ($siswaIds as $id) {
        $jobs[] = new SyncPembayaranSummarySiswaJob($id);
    }

    $batch = Bus::batch($jobs)->dispatch();

    return "Batch Berhasil Dibuat! ID: " . $batch->id . " <br> Silakan buka: /debug-check-batch/" . $batch->id;
});

// 2. Test Cek Batch secara Manual
Route::get('/debug-check-batch/{id}', function ($id) {
    $batch = Bus::findBatch($id);

    if (!$batch) {
        return "Gagal: Batch dengan ID {$id} tidak ditemukan di database.";
    }

    return response()->json([
        'id' => $batch->id,
        'total' => $batch->totalJobs,
        'pending' => $batch->pendingJobs,
        'failed' => $batch->failedJobs,
        'finished' => $batch->finished(),
    ]);
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