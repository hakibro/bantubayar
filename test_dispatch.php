<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Testing Job Dispatch & Execution ===\n\n";

// Test 1: Check if QUEUE_CONNECTION is really 'database'
echo "[1] Queue Connection: " . config('queue.default') . "\n";

// Test 2: Check current  queue driver
echo "[2] Current Queue Driver: " . config('queue.connections.' . config('queue.default') . '.driver') . "\n";

// Test 3: Try to dispatch a job and see what happens
echo "[3] Dispatching SyncPembayaranSiswaJob...\n";

try {
    // Use dispatch_now to see if it gets queued or executed
    $job = new \App\Jobs\SyncPembayaranSiswaJob('255046');

    // Check if it's queued
    \Illuminate\Support\Facades\Bus::dispatch($job);

    echo "  Job dispatched\n";

    // Check jobs table
    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "  Jobs in table: $count\n";

} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n[4] Checking DB configuration...\n";
$dbConfig = config('database.connections.mysql');
echo "  Host: " . $dbConfig['host'] . "\n";
echo "  Database: " . $dbConfig['database'] . "\n";
echo "  Connection: " . config('queue.connections.database.connection') . "\n";
