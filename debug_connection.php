<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: Queue Connection Details ===\n\n";

// Check what connection the queue is actually using
echo "[1] Queue Connection Check:\n";
echo "    Queue Connection Config (from env): " . env('DB_QUEUE_CONNECTION') . "\n";
echo "    Queue Connection Config (from queue config): " . config('queue.connections.database.connection') . "\n";

// Get the actual connection
try {
    $queueConnection = config('queue.connections.database.connection');
    $resolver = \Illuminate\Support\Facades\DB::getConnectionResolver();
    $connection = $resolver->connection($queueConnection);
    echo "    Actual Connection Name: " . $connection->getName() . "\n";
    echo "    Actual Database: " . $connection->getConfig('database') . "\n";
} catch (\Exception $e) {
    echo "    ERROR getting connection: " . $e->getMessage() . "\n";
}

echo "\n[2] Database Connections Available:\n";
$configs = config('database.connections');
foreach ($configs as $name => $config) {
    echo "    - $name\n";
}

echo "\n[3] Queue Manager Check:\n";
$manager = \Illuminate\Support\Facades\Queue::getConnectionResolver();
echo "    Resolver: " . get_class($manager) . "\n";

echo "\n[4] Attempting manual push to jobs table:\n";

// Try manual insert
try {
    $connection = \Illuminate\Support\Facades\DB::connection('mysql');

    $jobData = [
        'queue' => 'sync-pembayaran',
        'payload' => json_encode([
            'displayName' => 'SyncPembayaranSiswaJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['test' => 'data']
        ]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ];

    $id = $connection->table('jobs')->insertGetId($jobData);
    echo "    ✓ Manual insert successful, ID: $id\n";

    $count = $connection->table('jobs')->count();
    echo "    Jobs count after manual insert: $count\n";

} catch (\Exception $e) {
    echo "    ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";
