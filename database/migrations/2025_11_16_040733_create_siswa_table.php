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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('idperson')->unique();
            $table->string('nama');
            $table->enum('gender', ['L', 'P']);
            $table->string('lahirtempat')->nullable();
            $table->date('lahirtanggal')->nullable();
            $table->string('phone')->nullable();

            // Data Formal
            $table->string('UnitFormal')->nullable();
            $table->string('KelasFormal')->nullable();

            // Data Pondok
            $table->string('AsramaPondok')->nullable();
            $table->string('KamarPondok')->nullable();

            // Data Diniyah
            $table->string('TingkatDiniyah')->nullable();
            $table->string('KelasDiniyah')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
