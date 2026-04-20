<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('siswa_pembayaran', function (Blueprint $column) {
            // Menambahkan kolom summary tipe JSON setelah kolom kelas_info
            // nullable() digunakan agar data lama tidak error saat migrasi
            $column->json('summary')->nullable()->after('kelas_info');

            // Opsional: Tambahkan index jika Anda menggunakan MySQL 5.7+ atau PostgreSQL
            // untuk mempercepat pencarian di dalam data JSON
            // $column->index(['summary']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_pembayaran', function (Blueprint $column) {
            $column->dropColumn('summary');
        });
    }
};