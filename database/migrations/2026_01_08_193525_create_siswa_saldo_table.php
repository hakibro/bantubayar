<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa_saldo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                ->constrained('siswa')
                ->cascadeOnDelete();

            $table->decimal('saldo', 15, 2)
                ->default(0);

            $table->timestamps();

            $table->unique('siswa_id'); // 1 siswa = 1 saldo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_saldo');
    }
};
