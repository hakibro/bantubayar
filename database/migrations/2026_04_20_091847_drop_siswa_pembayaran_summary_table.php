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
        // Menghapus tabel jika ada
        Schema::dropIfExists('siswa_pembayaran_summary');
    }

    /**
     * Reverse the migrations.
     * Bagian down() harus berisi skema tabel lama agar migrasi bisa di-rollback jika perlu.
     */
    public function down(): void
    {
        Schema::create('siswa_pembayaran_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->string('periode');
            $table->json('summary_data'); // Sesuaikan dengan nama kolom lama Anda
            $table->timestamps();
        });
    }
};