<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: Job Dispatch Issue ===\n\n";

// 1. Check configuration
echo "[1] Configuration:\n";
echo "    QUEUE_CONNECTION: " . env('QUEUE_CONNECTION') . "\n";
echo "    CACHE_STORE: " . env('CACHE_STORE') . "\n";
echo "    DB_QUEUE_CONNECTION: " . env('DB_QUEUE_CONNECTION') . "\n";
echo "\n";

// 2. Check queue config
echo "[2] Queue Config:\n";
echo "    Default Queue: " . config('queue.default') . "\n";
echo "    Database Driver: " . config('queue.connections.database.driver') . "\n";
echo "    Database Connection: " . config('queue.connections.database.connection') . "\n";
echo "    Database Table: " . config('queue.connections.database.table') . "\n";
echo "\n";

// 3. Check database connection
echo "[3] Database Connection:\n";
try {
    $result = \Illuminate\Support\Facades\DB::connection('mysql')->getPdo();
    echo "    MySQL Connection: OK\n";
} catch (\Exception $e) {
    echo "    MySQL Connection: FAILED - " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Check jobs table
echo "[4] Jobs Table:\n";
try {
    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "    Total jobs: $count\n";

    // Check table structure
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('jobs');
    echo "    Columns: " . implode(', ', $columns) . "\n";
} catch (\Exception $e) {
    echo "    Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Test simple dispatch
echo "[5] Testing Simple Dispatch:\n";
try {
    // Clear existing
    \Illuminate\Support\Facades\DB::table('jobs')->truncate();
    \Illuminate\Support\Facades\Cache::flush();

    echo "    Dispatching test job...\n";

    // Create simple test job
    $job = new class implements \Illuminate\Contracts\Queue\ShouldQueue {
        use \Illuminate\Bus\Queueable;
        use \Illuminate\Foundation\Bus\Dispatchable;
        use \Illuminate\Queue\SerializesModels;
        use \Illuminate\Queue\InteractsWithQueue;

        public function handle()
        {
            // Do nothing
        }
    };

    dispatch($job);

    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "    Jobs after dispatch: $count\n";

    if ($count > 0) {
        $job = \Illuminate\Support\Facades\DB::table('jobs')->first();
        echo "    Job queue: " . $job->queue . "\n";
        echo "    Job status: OK\n";
    } else {
        echo "    WARNING: Job not in database - might be executing synchronously!\n";
    }

} catch (\Exception $e) {
    echo "    Error: " . $e->getMessage() . "\n";
    echo "    Stack: " . $e->getFile() . ':' . $e->getLine() . "\n";
}
echo "\n";

// 6. Check cache
echo "[6] Cache System:\n";
echo "    Cache Driver: " . config('cache.default') . "\n";
try {
    \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);
    $value = \Illuminate\Support\Facades\Cache::get('test_key');
    echo "    Cache Test: " . ($value === 'test_value' ? 'OK' : 'FAILED') . "\n";
} catch (\Exception $e) {
    echo "    Cache Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Debug complete\n";
