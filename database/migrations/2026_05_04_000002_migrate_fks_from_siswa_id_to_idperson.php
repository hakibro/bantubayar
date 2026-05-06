<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Nonaktifkan FK checks agar UPDATE bisa mengisi nilai idperson
        // yang belum tentu ada sebagai siswa.id
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Migrasi data: ganti nilai FK dari siswa.id (auto-increment) ke siswa.idperson
        DB::statement("
            UPDATE penanganan p
            JOIN siswa s ON s.id = p.id_siswa
            SET p.id_siswa = s.idperson
        ");

        DB::statement("
            UPDATE petugas_siswa ps
            JOIN siswa s ON s.id = ps.siswa_id
            SET ps.siswa_id = s.idperson
        ");

        DB::statement("
            UPDATE home_visits hv
            JOIN siswa s ON s.id = hv.siswa_id
            SET hv.siswa_id = s.idperson
        ");

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Hapus FK constraints (view tidak bisa dijadikan referensi FK)
        Schema::table('penanganan', function (Blueprint $table) {
            $table->dropForeign(['id_siswa']);
        });

        Schema::table('petugas_siswa', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
        });

        Schema::table('home_visits', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
        });
    }

    public function down(): void
    {
        // Catatan: data tidak di-rollback karena tidak bisa reverse JOIN siswa
        // Hanya restore FK constraints jika tabel siswa masih ada
        if (Schema::hasTable('siswa')) {
            Schema::table('penanganan', function (Blueprint $table) {
                $table->foreign('id_siswa')->references('id')->on('siswa')->onDelete('cascade');
            });
            Schema::table('petugas_siswa', function (Blueprint $table) {
                $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            });
            Schema::table('home_visits', function (Blueprint $table) {
                $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            });
        }
    }
};
