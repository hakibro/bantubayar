<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Test API untuk satu siswa
$service = $app->make('App\Services\SiswaService');

// Cek satu siswa di DB
$siswa = \App\Models\Siswa::first();

if (!$siswa) {
    echo "Tidak ada siswa di database\n";
    exit(1);
}

echo "=== Testing siswa: {$siswa->idperson} ===\n";

$result = $service->getPembayaranSiswa($siswa->idperson);

echo "Status: " . ($result['status'] ? 'OK' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";

if ($result['status']) {
    echo "Data: " . json_encode($result['data']) . "\n";
    echo "Data Count: " . count($result['data']) . "\n";
} else {
    echo "HTTP Code: " . ($result['http_code'] ?? 'N/A') . "\n";
}

echo "\n";
