<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('petugas_siswa', function (Blueprint $table) {
            $table->id();

            // FK ke users.id (bigint unsigned)
            $table->unsignedBigInteger('petugas_id');

            // FK ke siswa.id (bigint unsigned)
            $table->unsignedBigInteger('siswa_id');

            $table->timestamps();

            $table->foreign('petugas_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('siswa_id')
                ->references('id')
                ->on('siswa')
                ->onDelete('cascade');

            $table->unique(['petugas_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas_siswa');
    }
};
