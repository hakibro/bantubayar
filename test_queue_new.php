<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST: Queue::push() with new config ===\n\n";

// Clear
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();

// Check config
echo "[1] Config Check:\n";
echo "    after_commit: " . (\Illuminate\Support\Facades\Config::get('queue.connections.database.after_commit') ? 'true' : 'false') . "\n\n";

// Push 3 jobs
echo "[2] Pushing 3 jobs...\n";
for ($i = 1; $i <= 3; $i++) {
    \Illuminate\Support\Facades\Queue::connection('database')->push(new \App\Jobs\SyncPembayaranSiswaJob('TEST' . $i));
    echo "    Job $i pushed\n";
}

// Check count
$count = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "\n[3] Jobs in database: $count\n";

if ($count === 3) {
    echo "    ✅ SUCCESS!\n\n";

    // Show details
    echo "[4] Job Details:\n";
    $jobs = \Illuminate\Support\Facades\DB::table('jobs')->get();
    foreach ($jobs as $job) {
        echo "    - ID: " . $job->id . ", Queue: " . $job->queue . "\n";
    }
} else {
    echo "    ❌ FAILED - Expected 3, got $count\n";
}

echo "\n";
