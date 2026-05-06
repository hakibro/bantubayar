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
        // 1. Nonaktifkan foreign key check sementara (opsional, untuk keamanan ekstra)
        Schema::disableForeignKeyConstraints();

        // 2. Hapus tabel dari yang paling bergantung (anak) ke tabel utama (induk)
        Schema::dropIfExists('siswa_pembayaran');
        Schema::dropIfExists('siswa_saldo');
        Schema::dropIfExists('siswa');

        // 3. Aktifkan kembali foreign key check
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Catatan: Jika migrasi ini di-rollback, Anda harus mendefinisikan ulang skema 
        // tabel-tabel ini di sini jika ingin struktur lamanya kembali otomatis.
        // Jika tidak diperlukan, biarkan kosong atau beri komentar.
    }
};