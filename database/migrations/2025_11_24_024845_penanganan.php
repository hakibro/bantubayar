<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penanganan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_petugas')->constrained('users')->onDelete('cascade');
            $table->enum('jenis', ['chat', 'telepon', 'visit']);
            $table->text('catatan')->nullable();
            $table->json('rating')->nullable(); // format: {"angka":5,"sikap":"kooperatif","catatan":"langsung membayar"}
            $table->enum('hasil', ['lunas', 'rekom'])->nullable();
            $table->date('tanggal_rekom')->nullable();
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
