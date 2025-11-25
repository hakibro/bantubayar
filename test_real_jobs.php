<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TEST: Real Jobs with Real Siswa IDs ===\n\n";

// Clear
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();
\Illuminate\Support\Facades\Cache::flush();

// Get real siswa IDs
$siswa = \App\Models\Siswa::select('idperson')->limit(10)->get();

echo "[1] Creating jobs for 10 real siswa...\n";

use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncPembayaranSiswaJob;

$count = 0;
foreach ($siswa as $s) {
    try {
        $job = (new SyncPembayaranSiswaJob($s->idperson))->onQueue('sync-pembayaran');
        Queue::connection('database')->push($job);
        $count++;
        echo "    ✓ Job created for siswa: " . $s->idperson . "\n";
    } catch (\Exception $e) {
        echo "    ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n[2] Jobs in database: " . \Illuminate\Support\Facades\DB::table('jobs')->count() . "\n";

if ($count === 10) {
    echo "    ✅ Ready to process!\n";
} else {
    echo "    ❌ Only $count jobs created\n";
}

echo "\n";
