<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: Queue Status ===\n\n";

// Truncate and clear
echo "[1] Cleaning tables...\n";
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();
echo "    ✓ Cleared\n\n";

// Config check
echo "[2] Configuration:\n";
$queueDriver = config('queue.default');
$queueConnName = config('queue.connections.database.connection');
echo "    Default Queue Driver: " . $queueDriver . "\n";
echo "    Database Queue Connection: " . $queueConnName . "\n";
echo "    Database Queue Table: " . config('queue.connections.database.table') . "\n";
echo "    After Commit: " . (config('queue.connections.database.after_commit') ? 'true' : 'false') . "\n\n";

// Try dispatch with logging
echo "[3] Testing dispatch with Bus:\n";

try {
    $job = new \App\Jobs\SyncPembayaranSiswaJob('TEST123');

    // Use Bus facade
    $result = \Illuminate\Support\Facades\Bus::dispatch($job);

    echo "    ✓ Job dispatched\n";
    echo "    Result: " . (is_string($result) ? $result : get_class($result)) . "\n\n";

    // Check immediate
    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "[4] Jobs in database: $count\n";

    if ($count > 0) {
        echo "    ✅ SUCCESS! Job is in database!\n";
        $record = \Illuminate\Support\Facades\DB::table('jobs')->first();
        echo "    Queue: " . $record->queue . "\n";
        echo "    Payload sample: " . substr($record->payload, 0, 80) . "...\n";
    } else {
        echo "    ❌ PROBLEM: Job NOT in database\n";

        // Check if it's in failed_jobs
        $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        echo "    Failed jobs: $failedCount\n";

        // Try manual dispatch via helper
        echo "\n[5] Trying dispatch() helper:\n";
        dispatch(new \App\Jobs\SyncPembayaranSiswaJob('TEST456'));

        $count2 = \Illuminate\Support\Facades\DB::table('jobs')->count();
        echo "    Jobs after dispatch() helper: $count2\n";
    }

} catch (\Throwable $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
    echo "    File: " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\n";
