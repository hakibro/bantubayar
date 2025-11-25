<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST: Queue name in job ===\n\n";

// Check job's queue method
$job = new \App\Jobs\SyncPembayaranSiswaJob('TEST');

echo "[1] Job queue() method:\n";
echo "    Returns: " . $job->queue() . "\n\n";

// Push and check
\Illuminate\Support\Facades\DB::table('jobs')->truncate();

\Illuminate\Support\Facades\Queue::connection('database')->push($job);

$record = \Illuminate\Support\Facades\DB::table('jobs')->first();
echo "[2] Job in database:\n";
echo "    Queue name: " . $record->queue . "\n";
echo "    Payload: " . substr($record->payload, 0, 150) . "...\n";

// The issue: Dispatchable trait doesn't respect queue() method when using push()
// We need to use onQueue() or dispatch() with onQueue()

echo "\n[3] Testing with onQueue():\n";

\Illuminate\Support\Facades\DB::table('jobs')->truncate();

$job2 = (new \App\Jobs\SyncPembayaranSiswaJob('TEST2'))->onQueue('sync-pembayaran');
\Illuminate\Support\Facades\Queue::connection('database')->push($job2);

$record2 = \Illuminate\Support\Facades\DB::table('jobs')->first();
echo "    Queue name: " . $record2->queue . "\n";

echo "\n";
