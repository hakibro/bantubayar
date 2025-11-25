<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Clear all jobs and failed jobs
\Illuminate\Support\Facades\DB::table('jobs')->truncate();
\Illuminate\Support\Facades\DB::table('failed_jobs')->truncate();

// Clear cache
\Illuminate\Support\Facades\Cache::flush();

echo "✅ All queue jobs cleared!\n";
echo "✅ All failed jobs cleared!\n";
echo "✅ All cache cleared!\n";
