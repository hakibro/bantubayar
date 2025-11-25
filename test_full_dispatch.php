<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST: Full Dispatch Simulation ===\n\n";

// Clear
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();
\Illuminate\Support\Facades\Cache::flush();

echo "[1] Simulating controller dispatch for 10 jobs...\n";

use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncPembayaranSiswaJob;

for ($i = 1; $i <= 10; $i++) {
    $idperson = 'SIM' . str_pad($i, 4, '0', STR_PAD_LEFT);
    try {
        $job = (new SyncPembayaranSiswaJob($idperson))->onQueue('sync-pembayaran');
        Queue::connection('database')->push($job);
        echo "    ✓ Job $i dispatched for $idperson\n";
    } catch (\Exception $e) {
        echo "    ❌ Job $i failed: " . $e->getMessage() . "\n";
    }
}

echo "\n[2] Checking database:\n";

$count = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Total jobs: $count\n";

if ($count === 10) {
    echo "    ✅ All 10 jobs are in database!\n\n";

    echo "[3] Job Details:\n";
    $jobs = \Illuminate\Support\Facades\DB::table('jobs')->select('id', 'queue', 'attempts', 'available_at')->get();
    foreach ($jobs as $job) {
        echo "    - ID: " . $job->id . ", Queue: " . $job->queue . ", Attempts: " . $job->attempts . "\n";
    }

    echo "\n[4] Ready for queue worker:\n";
    echo "    Run: php artisan queue:work --queue=sync-pembayaran\n";
} else {
    echo "    ❌ Expected 10, but got $count\n";
}

echo "\n";
