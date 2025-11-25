<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Database Query Test ===\n\n";

// Direct query to jobs table
$allJobs = \Illuminate\Support\Facades\DB::table('jobs')->get();
echo "[1] All jobs in 'jobs' table: " . count($allJobs) . "\n";

// Query specific queue
$queueJobs = \Illuminate\Support\Facades\DB::table('jobs')
    ->where('queue', 'sync-pembayaran')
    ->get();
echo "[2] Jobs with queue='sync-pembayaran': " . count($queueJobs) . "\n";

// Query default queue
$defaultJobs = \Illuminate\Support\Facades\DB::table('jobs')
    ->where('queue', 'default')
    ->get();
echo "[3] Jobs with queue='default': " . count($defaultJobs) . "\n";

// Check all queues
$allQueues = \Illuminate\Support\Facades\DB::table('jobs')
    ->distinct('queue')
    ->pluck('queue');
echo "[4] All distinct queues: " . $allQueues->implode(', ') . "\n";

// Check table structure
echo "\n[5] Table columns:\n";
$columns = \Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('jobs');
foreach ($columns as $col) {
    echo "  - $col\n";
}

echo "\nNote: If no jobs appear, the queue might be using SYNC driver!\n";
echo "Check QUEUE_CONNECTION in .env\n";
