<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Clear cache & failed jobs
\Illuminate\Support\Facades\Cache::flush();

echo "=== Testing Queue Job Dispatch ===\n\n";

// Dispatch DispatchSyncPembayaranJob
echo "[1] Dispatching DispatchSyncPembayaranJob...\n";
\App\Jobs\DispatchSyncPembayaranJob::dispatch();

echo "[2] Checking queue jobs...\n";
$jobCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "Total jobs in queue: $jobCount\n";

echo "[3] Checking cache...\n";
$total = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_total', 0);
$processed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_processed', 0);
$failed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_failed', 0);

echo "Total siswa: $total\n";
echo "Processed: $processed\n";
echo "Failed: $failed\n";

echo "\n✅ Job dispatch successful!\n";
echo "Now run queue worker:\n";
echo "  php artisan queue:work database --queue=sync-pembayaran --tries=3 --timeout=600\n";
