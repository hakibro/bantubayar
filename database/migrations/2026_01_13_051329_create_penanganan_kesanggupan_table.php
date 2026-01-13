<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penanganan_kesanggupan', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke penanganan
            $table->foreignId('penanganan_id')
                ->constrained('penanganan')
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->decimal('nominal', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penanganan_kesanggupan');
    }
};
