<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {

            // Ubah enum hasil (hapus enum rekomendasi)
            $table->enum('hasil', [
                'lunas',
                'isi_saldo',
                'tidak_ada_respon',
                'cicilan',
                'hp_tidak_aktif',
            ])->nullable()->change();

            // Hapus kolom yang tidak dipakai
            if (Schema::hasColumn('penanganan', 'tanggal_rekom')) {
                $table->dropColumn('tanggal_rekom');
            }

            if (Schema::hasColumn('penanganan', 'kesanggupan')) {
                $table->dropColumn('kesanggupan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {

            // Kembalikan enum lama (SESUAIKAN jika berbeda)
            $table->enum('hasil', [
                'rekomendasi',
                'tidak_rekomendasi'
            ])->change();

            // Kembalikan kolom yang dihapus
            $table->date('tanggal_rekom')->nullable();
            $table->string('kesanggupan')->nullable();
        });
    }
};
