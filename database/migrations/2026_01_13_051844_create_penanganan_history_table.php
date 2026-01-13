<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penanganan_history', function (Blueprint $table) {
            $table->id();

            // Foreign key ke penanganan
            $table->foreignId('penanganan_id')
                ->constrained('penanganan')
                ->cascadeOnDelete();

            $table->string('jenis_penanganan');
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penanganan_history');
    }
};
