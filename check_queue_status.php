<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Queue Status After Worker Run ===\n\n";

$jobsCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
$failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
$processed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_processed', 0);
$failed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_failed', 0);

echo "Jobs in queue: $jobsCount\n";
echo "Failed jobs: $failedCount\n";
echo "Processed (from cache): $processed\n";
echo "Failed (from cache): $failed\n";

if ($jobsCount === 0 && $failedCount === 0 && $processed === 10) {
    echo "\n✅ SUCCESS! All 10 jobs were processed!\n";
} elseif ($failedCount > 0) {
    echo "\n⚠️  Some jobs failed. Checking details...\n\n";
    $failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->get();
    foreach ($failed as $job) {
        echo "Failed Job:\n";
        echo "  Exception: " . substr($job->exception, 0, 150) . "...\n";
    }
} else {
    echo "\n❌ Something else happened...\n";
}

echo "\n";
