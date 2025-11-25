<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DEBUG: dispatch() vs connection->push() ===\n\n";

\Illuminate\Support\Facades\DB::table('jobs')->truncate();

echo "[1] Using dispatch() helper:\n";
dispatch(new \App\Jobs\SyncPembayaranSiswaJob('DISPATCH1'));
$count1 = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs in table: $count1\n";

echo "\n[2] Using connection->push():\n";
$connection = \Illuminate\Support\Facades\Queue::connection('database');
$connection->push(new \App\Jobs\SyncPembayaranSiswaJob('PUSH1'));
$count2 = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs in table: $count2\n";

echo "\n[3] Using Bus::dispatch():\n";
\Illuminate\Support\Facades\Bus::dispatch(new \App\Jobs\SyncPembayaranSiswaJob('BUSDISPATCH1'));
$count3 = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs in table: $count3\n";

echo "\n[4] Using Queue::push():\n";
\Illuminate\Support\Facades\Queue::push(new \App\Jobs\SyncPembayaranSiswaJob('QUEUEPUSH1'));
$count4 = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs in table: $count4\n";

echo "\n[5] Testing with onQueue:\n";
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
dispatch((new \App\Jobs\SyncPembayaranSiswaJob('ONQUEUE1'))->onQueue('sync-pembayaran'));
$count5 = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "    Jobs in table: $count5\n";

echo "\n";
