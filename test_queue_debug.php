<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Checking Queue Connection ===\n\n";

// Test dispatch job directly
echo "[1] Dispatching test job...\n";
\App\Jobs\SyncPembayaranSiswaJob::dispatch('255046');

echo "[2] Checking jobs table...\n";
$jobs = \Illuminate\Support\Facades\DB::table('jobs')->get();
echo "Total rows: " . count($jobs) . "\n";

if (count($jobs) > 0) {
    echo "Last job:\n";
    $job = $jobs->last();
    echo "  ID: " . $job->id . "\n";
    echo "  Queue: " . $job->queue . "\n";
    echo "  Payload: " . substr($job->payload, 0, 100) . "...\n";
}

echo "\n[3] Checking queue connection config...\n";
echo "QUEUE_CONNECTION: " . env('QUEUE_CONNECTION') . "\n";

$config = config('queue.connections.database');
echo "Driver: " . $config['driver'] . "\n";
echo "Table: " . $config['table'] . "\n";
echo "Queue: " . $config['queue'] . "\n";
