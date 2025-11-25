<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              QUEUE SYSTEM VERIFICATION REPORT                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$checks = [
    [
        'name' => 'Configuration: after_commit setting',
        'test' => fn() => \Illuminate\Support\Facades\Config::get('queue.connections.database.after_commit') === false,
        'expected' => 'false',
    ],
    [
        'name' => 'Configuration: Queue driver',
        'test' => fn() => \Illuminate\Support\Facades\Config::get('queue.default') === 'database',
        'expected' => 'database',
    ],
    [
        'name' => 'Configuration: Database connection',
        'test' => fn() => \Illuminate\Support\Facades\Config::get('queue.connections.database.connection') === 'mysql',
        'expected' => 'mysql',
    ],
    [
        'name' => 'Database: MySQL connection',
        'test' => function () {
            try {
                \Illuminate\Support\Facades\DB::connection('mysql')->getPdo();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        },
        'expected' => 'connected',
    ],
    [
        'name' => 'Database: jobs table exists',
        'test' => fn() => \Illuminate\Support\Facades\Schema::hasTable('jobs'),
        'expected' => 'yes',
    ],
    [
        'name' => 'Database: failed_jobs table exists',
        'test' => fn() => \Illuminate\Support\Facades\Schema::hasTable('failed_jobs'),
        'expected' => 'yes',
    ],
    [
        'name' => 'Cache: Database store available',
        'test' => function () {
            try {
                \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);
                $value = \Illuminate\Support\Facades\Cache::get('test_key');
                return $value === 'test_value';
            } catch (\Exception $e) {
                return false;
            }
        },
        'expected' => 'working',
    ],
    [
        'name' => 'Job Class: SyncPembayaranSiswaJob exists',
        'test' => fn() => class_exists('App\Jobs\SyncPembayaranSiswaJob'),
        'expected' => 'yes',
    ],
    [
        'name' => 'Job Class: queue() method returns correct value',
        'test' => function () {
            $job = new \App\Jobs\SyncPembayaranSiswaJob(123);
            return $job->queue() === 'sync-pembayaran';
        },
        'expected' => 'sync-pembayaran',
    ],
    [
        'name' => 'Controller: Queue import present',
        'test' => function () {
            $code = file_get_contents(__DIR__ . '/app/Http/Controllers/Admin/SyncPembayaranController.php');
            return strpos($code, 'use Illuminate\Support\Facades\Queue;') !== false;
        },
        'expected' => 'imported',
    ],
    [
        'name' => 'Controller: Uses Queue::connection()->push()',
        'test' => function () {
            $code = file_get_contents(__DIR__ . '/app/Http/Controllers/Admin/SyncPembayaranController.php');
            return strpos($code, "Queue::connection('database')->push") !== false;
        },
        'expected' => 'used',
    ],
];

$passCount = 0;
$failCount = 0;

foreach ($checks as $check) {
    try {
        $result = $check['test']();
        if ($result) {
            echo "✅ PASS: " . $check['name'] . "\n";
            $passCount++;
        } else {
            echo "❌ FAIL: " . $check['name'] . " (expected: " . $check['expected'] . ")\n";
            $failCount++;
        }
    } catch (\Exception $e) {
        echo "⚠️  ERROR: " . $check['name'] . " (" . $e->getMessage() . ")\n";
        $failCount++;
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        SUMMARY REPORT                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Total Checks: " . ($passCount + $failCount) . "\n";
echo "✅ Passed: $passCount\n";
echo "❌ Failed: $failCount\n\n";

if ($failCount === 0) {
    echo "🎉 ALL CHECKS PASSED - SYSTEM READY!\n\n";
    echo "Next Steps:\n";
    echo "1. Go to: http://localhost:8000/admin/sync-pembayaran\n";
    echo "2. Click 'Mulai' button to start synchronization\n";
    echo "3. In terminal, run: php artisan queue:work --queue=default\n";
    echo "4. Monitor progress in the web interface\n";
} else {
    echo "⚠️  SOME CHECKS FAILED - REVIEW AND FIX BEFORE USING\n";
}

echo "\n";
