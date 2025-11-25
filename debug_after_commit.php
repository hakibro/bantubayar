<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: After Commit Behavior ===\n\n";

\Illuminate\Support\Facades\DB::table('jobs')->truncate();

echo "[1] Testing dispatch WITHIN transaction:\n";

try {
    \Illuminate\Support\Facades\DB::beginTransaction();

    echo "    Transaction started\n";

    dispatch(new \App\Jobs\SyncPembayaranSiswaJob('TXN1'));

    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "    Jobs BEFORE commit: $count\n";

    \Illuminate\Support\Facades\DB::commit();

    $count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo "    Jobs AFTER commit: $count\n";

} catch (\Exception $e) {
    \Illuminate\Support\Facades\DB::rollback();
    echo "    ERROR: " . $e->getMessage() . "\n";
}

echo "\n[2] Testing dispatch WITHOUT transaction:\n";

\Illuminate\Support\Facades\DB::table('jobs')->truncate();

dispatch(new \App\Jobs\SyncPembayaranSiswaJob('NOTXN1'));

$count = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs immediately: $count\n";

echo "\n[3] Checking Laravel Debugbar/Profiling State:\n";
echo "    In transaction: " . (\Illuminate\Support\Facades\DB::transactionLevel() > 0 ? 'Yes' : 'No') . "\n";

echo "\n[4] Setting after_commit to false and testing:\n";

// Modify config at runtime
\Illuminate\Support\Facades\Config::set('queue.connections.database.after_commit', false);
echo "    after_commit set to: " . (\Illuminate\Support\Facades\Config::get('queue.connections.database.after_commit') ? 'true' : 'false') . "\n";

\Illuminate\Support\Facades\DB::table('jobs')->truncate();

dispatch(new \App\Jobs\SyncPembayaranSiswaJob('NOAFTER1'));

$count = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs after setting to false: $count\n";

if ($count > 0) {
    echo "    ✅ Jobs appeared when after_commit=false!\n";
    $job = \Illuminate\Support\Facades\DB::table('jobs')->first();
    echo "    Queue: " . $job->queue . "\n";
}

echo "\n";
