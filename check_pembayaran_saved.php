<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Check Pembayaran Data Saved ===\n\n";

$siswa = \App\Models\Siswa::whereIn('idperson', [190013, 190015, 190026, 190028])->get();

foreach ($siswa as $s) {
    if ($s->pembayaran) {
        $data = json_decode($s->pembayaran, true);
        $count = count($data ?? []);
        echo "✓ Siswa {$s->idperson}: Pembayaran saved ($count items)\n";
    } else {
        echo "✗ Siswa {$s->idperson}: No pembayaran\n";
    }
}

echo "\n";
