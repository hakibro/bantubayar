<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Get Real Siswa IDs ===\n\n";

$siswa = \App\Models\Siswa::select('id', 'idperson')->limit(5)->get();

if ($siswa->isEmpty()) {
    echo "No siswa in database!\n";
} else {
    echo "First 5 siswa:\n";
    foreach ($siswa as $s) {
        echo "  ID: " . $s->id . ", idperson: " . $s->idperson . " (type: " . gettype($s->idperson) . ")\n";
    }
}

// Check siswa table schema
echo "\n=== Siswa Table Schema ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('siswa');
echo "Columns: " . implode(', ', $columns) . "\n";

// Check idperson column type
$column = \Illuminate\Support\Facades\DB::connection()->getDoctrineColumn('siswa', 'idperson');
echo "idperson type: " . $column->getType()->getName() . "\n";

echo "\n";
