<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Test API
$service = $app->make('App\Services\SiswaService');
$result = $service->testConnection();

echo "=== API Connection Test ===\n";
echo "Status: " . ($result['status'] ? 'OK' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";
echo "HTTP Code: " . $result['http_code'] . "\n";
if (isset($result['total'])) {
    echo "Total Siswa: " . $result['total'] . "\n";
}

if (!$result['status']) {
    echo "\nError Body:\n";
    echo $result['error_body'] ?? $result['error'] ?? 'No detail';
}

echo "\n";
