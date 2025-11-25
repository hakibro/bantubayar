<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== FINAL TEST: End-to-End Flow ===\n\n";

// Clear
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();
\Illuminate\Support\Facades\Cache::flush();

echo "[1] Simulating controller->start() with first 20 siswa...\n\n";

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SyncPembayaranSiswaJob;
use App\Models\Siswa;

// Simulate controller logic
$total = Siswa::count();
Cache::put('sync_pembayaran_status', 'running', now()->addHours(1));
Cache::put('sync_pembayaran_total', $total);
Cache::put('sync_pembayaran_processed', 0);
Cache::put('sync_pembayaran_failed', 0);

echo "Total siswa: $total\n";
echo "Dispatching first 20 siswa only for testing...\n\n";

$count = 0;
Siswa::select('id', 'idperson')->limit(20)->chunk(100, function ($chunk) use (&$count) {
    foreach ($chunk as $siswa) {
        try {
            $job = (new SyncPembayaranSiswaJob($siswa->idperson))->onQueue('sync-pembayaran');
            Queue::connection('database')->push($job);
            $count++;
            if ($count <= 5) {
                echo "  ✓ Job dispatched for siswa: {$siswa->idperson}\n";
            }
        } catch (\Exception $e) {
            echo "  ❌ Error for {$siswa->idperson}: " . $e->getMessage() . "\n";
        }
    }
});

echo "  ... (15 more)\n\n";

$jobsCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "[2] Jobs in database: $jobsCount\n";

if ($jobsCount === 20) {
    echo "    ✅ All 20 jobs dispatched successfully!\n\n";
    echo "[3] Ready for queue:work\n";
    echo "    Command: php artisan queue:work --queue=default\n\n";
} else {
    echo "    ❌ Expected 20, got $jobsCount\n";
}

echo "=== Test Complete ===\n";
