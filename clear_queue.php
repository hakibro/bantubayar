<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('jobs')->delete();
DB::table('failed_jobs')->delete();
echo "Queue cleared successfully\n";
