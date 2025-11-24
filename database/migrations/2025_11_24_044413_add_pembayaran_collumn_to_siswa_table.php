<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->json('pembayaran')->nullable()->after('KelasDiniyah');
            // ganti 'some_column' dengan kolom terakhir sebelum pembayaran
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });
    }
};
