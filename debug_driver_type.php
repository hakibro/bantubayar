<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: Queue Driver Type ===\n\n";

// Get the actual queue connection instance
$queueManager = app('queue');
$connection = $queueManager->connection('database');

echo "[1] Queue Connection Class: " . get_class($connection) . "\n";
echo "[2] Queue Connection Driver: " . ($connection->driver ?? 'N/A') . "\n";

// Check all properties/methods
echo "\n[3] Queue Connection Methods:\n";
$reflection = new \ReflectionClass($connection);
$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
foreach ($methods as $method) {
    if (
        strpos($method->getName(), 'push') !== false ||
        strpos($method->getName(), 'pop') !== false ||
        strpos($method->getName(), 'dispatch') !== false ||
        strpos($method->getName(), 'batch') !== false
    ) {
        echo "    - " . $method->getName() . "\n";
    }
}

// Try explicit push
echo "\n[4] Testing explicit push method:\n";

\Illuminate\Support\Facades\DB::table('jobs')->truncate();

try {
    $job = new \App\Jobs\SyncPembayaranSiswaJob('EXPLICIT1');

    // Try push directly on connection
    if (method_exists($connection, 'push')) {
        echo "    Calling push() directly...\n";
        $result = $connection->push($job);
        echo "    Result: " . $result . "\n";
    } else {
        echo "    push() method not available\n";
    }

    // Check queue driver class
    echo "    Queue driver full namespace: " . get_class($connection) . "\n";

    // Check if it's actually DatabaseQueue
    if (strpos(get_class($connection), 'DatabaseQueue') !== false) {
        echo "    ✓ It IS DatabaseQueue\n";
    } elseif (strpos(get_class($connection), 'SyncQueue') !== false) {
        echo "    ❌ It's SyncQueue! This executes jobs immediately!\n";
    } else {
        echo "    ? Unknown queue type: " . get_class($connection) . "\n";
    }

    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "    Jobs in table: $count\n";

} catch (\Exception $e) {
    echo "    ERROR: " . $e->getMessage() . "\n";
}

echo "\n";
