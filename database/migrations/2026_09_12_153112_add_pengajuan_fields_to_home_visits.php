<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_visits', function (Blueprint $table) {
            // Referensi ke penanganan yang memicu home visit (nullable, tidak pakai FK
            // karena kolom id_siswa pada penanganan sudah di-drop FK ke siswa).
            $table->unsignedBigInteger('penanganan_id')->nullable()->after('admin_id');
            $table->foreign('penanganan_id')->references('id')->on('penanganan')->onDelete('set null');

            // Siapa yang mengajukan & menyetujui.
            $table->unsignedBigInteger('diajukan_oleh')->nullable()->after('penanganan_id');
            $table->foreign('diajukan_oleh')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('disetujui_oleh')->nullable()->after('diajukan_oleh');
            $table->foreign('disetujui_oleh')->references('id')->on('users')->onDelete('set null');

            $table->timestamp('disetujui_at')->nullable()->after('disetujui_oleh');

            // Alasan pengajuan (wajib saat override manual / kategori "lainnya").
            $table->text('alasan_pengajuan')->nullable()->after('tanggal_visit');

            // Petugas home-visit bersifat opsional: bisa diisi nama/HP, atau cukup share link.
            $table->string('petugas_nama')->nullable()->change();
            $table->string('petugas_hp')->nullable()->change();
        });

        // Perluas enum status agar mendukung alur pengajuan → approval → selesai.
        DB::statement("
            ALTER TABLE home_visits
            MODIFY status ENUM(
                'pending',
                'disetujui',
                'ditolak',
                'dijadwalkan',
                'dilaksanakan',
                'selesai',
                'batal'
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE home_visits
            MODIFY status ENUM('dijadwalkan','dilaksanakan','selesai','batal')
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dijadwalkan'
        ");

        Schema::table('home_visits', function (Blueprint $table) {
            $table->dropForeign(['penanganan_id']);
            $table->dropForeign(['diajukan_oleh']);
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn(['penanganan_id', 'diajukan_oleh', 'disetujui_oleh', 'disetujui_at', 'alasan_pengajuan']);
            $table->string('petugas_nama')->nullable(false)->change();
            $table->string('petugas_hp')->nullable(false)->change();
        });
    }
};
