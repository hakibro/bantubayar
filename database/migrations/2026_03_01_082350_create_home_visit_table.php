<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('home_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('petugas_nama');
            $table->string('petugas_hp');
            $table->string('token')->unique();
            $table->date('tanggal_visit')->nullable();
            $table->enum('status', ['dijadwalkan', 'dilaksanakan', 'selesai', 'batal'])->default('dijadwalkan');
            $table->json('laporan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_visits');
    }
};