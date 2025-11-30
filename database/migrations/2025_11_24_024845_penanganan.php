<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penanganan', function (Blueprint $table) {
            $table->id();

            // Relasi siswa & petugas
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_petugas')->constrained('users')->onDelete('cascade');

            // Jenis pembayaran: diisi berdasarkan siswa_pembayaran yang belum lunas
            // format contoh: "SPP 2024 - Januari", "Gedung 2024", dll.
            $table->json('jenis_pembayaran');

            // Jenis penanganan
            $table->enum('jenis_penanganan', ['chat', 'telepon', 'visit']);

            // Catatan petugas
            $table->text('catatan')->nullable();

            // Rating JSON
            // {"angka": 5, "sikap": "kooperatif", "catatan": "langsung membayar"}
            $table->json('rating')->nullable();

            // Hasil: lunas atau rekom
            $table->enum('hasil', ['lunas', 'rekom'])->nullable();

            // Wajib jika hasil = rekom
            $table->date('tanggal_rekom')->nullable();

            // Status progress penanganan
            $table->enum('status', ['belum', 'proses', 'selesai'])->default('belum');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penanganan');
    }
};
