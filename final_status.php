<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== FINAL STATUS AFTER PROCESSING ===\n\n";

$jobsCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
$failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
$processed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_processed', 0);
$failed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_failed', 0);
$total = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_total', 0);
$isRunning = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_status') === 'running';

echo "Jobs in queue: $jobsCount\n";
echo "Failed jobs: $failedCount\n";
echo "Cache - Processed: $processed\n";
echo "Cache - Failed: $failed\n";
echo "Cache - Total: $total\n";
echo "Cache - Running: " . ($isRunning ? 'Yes' : 'No') . "\n";

echo "\n";

if ($jobsCount === 0 && $failedCount === 0 && $processed === 20) {
    echo "✅ SUCCESS!\n";
    echo "   - All 20 jobs processed from queue\n";
    echo "   - No failed jobs\n";
    echo "   - Cache counters correctly incremented\n";
    echo "   - Ready for production use\n";
} else {
    echo "⚠️  Status:\n";
    if ($jobsCount > 0)
        echo "   - Still $jobsCount jobs in queue\n";
    if ($failedCount > 0)
        echo "   - $failedCount jobs failed\n";
    if ($processed !== 20)
        echo "   - Expected 20 processed, got $processed\n";
}

echo "\n";
