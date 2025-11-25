<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Detailed Dispatch Test ===\n\n";

// Step 1: Clear everything
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();
\Illuminate\Support\Facades\Cache::flush();

echo "[1] Queue cleared\n";

// Step 2: Get sample siswa
$siswa = \App\Models\Siswa::select('id', 'idperson')->limit(5)->get();
echo "[2] Sample siswa:\n";
foreach ($siswa as $s) {
    echo "  - ID: {$s->id}, idperson: {$s->idperson}\n";
}

// Step 3: Simulate controller dispatch
echo "\n[3] Simulating controller dispatch...\n";

$total = 6102;
$dispatched = 0;

$siswaList = \App\Models\Siswa::select('id', 'idperson')->limit(10)->get();
foreach ($siswaList as $s) {
    \App\Jobs\SyncPembayaranSiswaJob::dispatch($s->idperson);
    $dispatched++;
}

echo "  Dispatched: $dispatched jobs\n";

// Step 4: Check queue
echo "\n[4] Checking queue...\n";
$jobs = \Illuminate\Support\Facades\DB::table('jobs')->get();
echo "  Jobs in table: " . count($jobs) . "\n";

if (count($jobs) > 0) {
    echo "  First job queue: " . $jobs[0]->queue . "\n";
    echo "  First job payload (first 100 chars): " . substr($jobs[0]->payload, 0, 100) . "\n";
}

echo "\n✅ Test complete!\n";
