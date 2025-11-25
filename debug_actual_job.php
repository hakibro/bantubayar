<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: Actual Job Dispatch ===\n\n";

// Clear existing
echo "[1] Clearing old jobs...\n";
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();
echo "    ✓ Jobs table cleared\n\n";

// Test with actual SyncPembayaranSiswaJob
echo "[2] Dispatching SyncPembayaranSiswaJob...\n";

use App\Jobs\SyncPembayaranSiswaJob;

try {
    // Dispatch single job
    $job = new SyncPembayaranSiswaJob('1');
    \Illuminate\Support\Facades\Bus::dispatch($job);

    echo "    ✓ Job dispatched\n\n";

    // Check if in database
    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "[3] Jobs table count: $count\n";

    if ($count > 0) {
        $record = \Illuminate\Support\Facades\DB::table('jobs')->first();
        echo "    Queue: " . $record->queue . "\n";
        echo "    Attempts: " . $record->attempts . "\n";
        echo "    Payload: " . substr($record->payload, 0, 100) . "...\n";
    } else {
        echo "    ⚠️  WARNING: Job NOT in database!\n";
        echo "    Checking if it executed synchronously...\n";

        // Check failed_jobs
        $failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        echo "    Failed jobs count: $failed\n";

        // Check cache
        $sync_count = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_processed', 0);
        $sync_failed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_failed', 0);
        echo "    Sync cache processed: $sync_count\n";
        echo "    Sync cache failed: $sync_failed\n";
    }

} catch (\Exception $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
    echo "    File: " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\n";

// Test direct dispatch call
echo "[4] Testing dispatch() helper directly...\n";

try {
    dispatch(new SyncPembayaranSiswaJob('2'));
    echo "    ✓ dispatch() called\n";

    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "    Jobs table count now: $count\n";

} catch (\Exception $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n✅ Debug complete\n";
