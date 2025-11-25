<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Testing Job Execution ===\n\n";

// Create a mock job handler to see if it's being called
$testLog = [];

class TestJob implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable;
    use \Illuminate\Foundation\Bus\Dispatchable;
    use \Illuminate\Queue\SerializesModels;
    use \Illuminate\Queue\InteractsWithQueue;

    public function handle()
    {
        global $testLog;
        $testLog[] = 'Job executed at ' . date('Y-m-d H:i:s');
    }
}

echo "[1] Dispatching TestJob...\n";
TestJob::dispatch();

if (!empty($testLog)) {
    echo "[2] Job WAS EXECUTED (Synchronous mode):\n";
    foreach ($testLog as $log) {
        echo "  $log\n";
    }
} else {
    echo "[2] Job was NOT executed (Queued mode):\n";
    echo "  Check jobs table: " . \Illuminate\Support\Facades\DB::table('jobs')->count() . " rows\n";
}

// Now try with SyncPembayaranSiswaJob and check if handler is called
echo "\n[3] Testing SyncPembayaranSiswaJob...\n";

$beforeCount = \Illuminate\Support\Facades\DB::table('siswa')->count();
echo "  Siswa count before: $beforeCount\n";

// Try to dispatch
try {
    \App\Jobs\SyncPembayaranSiswaJob::dispatch('255046');
    echo "  Job dispatch completed\n";

    // Check if jobs table has new entry
    $jobsCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "  Jobs in queue: $jobsCount\n";

} catch (\Exception $e) {
    echo "  Exception: " . $e->getMessage() . "\n";
}

echo "\n[4] Check cache connection:\n";
echo "  CACHE_STORE: " . env('CACHE_STORE') . "\n";
echo "  Cache driver: " . config('cache.default') . "\n";
