<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('siswa_pembayaran', function (Blueprint $table) {
            $table->boolean('is_lunas')->default(false)->after('data');
            $table->index('is_lunas'); // Penting untuk performa filter
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_pembayaran', function (Blueprint $table) {
            //
        });
    }
};
