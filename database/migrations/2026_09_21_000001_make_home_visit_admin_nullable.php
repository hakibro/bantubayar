<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_visits', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('Rollback memerlukan penanganan manual untuk home visit tanpa admin_id.');
    }
};
