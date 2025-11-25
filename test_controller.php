<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate calling the controller start() method
echo "=== Testing Controller Start Method ===\n\n";

$controller = new \App\Http\Controllers\Admin\SyncPembayaranController();

// Simulate request
$request = new \Illuminate\Http\Request();

$response = $controller->start();
$data = json_decode($response->content(), true);

echo "Status: " . ($data['status'] ? 'OK' : 'FAILED') . "\n";
echo "Message: " . $data['message'] . "\n";

echo "\n[Checking Queue]\n";
$jobCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "Jobs in queue: $jobCount\n";

echo "\n[Checking Cache]\n";
$total = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_total', 0);
$processed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_processed', 0);
$failed = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_failed', 0);
$status = \Illuminate\Support\Facades\Cache::get('sync_pembayaran_status', 'N/A');

echo "Total siswa: $total\n";
echo "Processed: $processed\n";
echo "Failed: $failed\n";
echo "Status: $status\n";

echo "\n✅ Success! Now run queue worker:\n";
echo "  php artisan queue:work database --queue=sync-pembayaran --tries=3 --timeout=600\n";
