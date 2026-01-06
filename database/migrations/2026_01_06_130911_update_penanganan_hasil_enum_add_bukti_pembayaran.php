<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tambah kolom bukti pembayaran
        Schema::table('penanganan', function (Blueprint $table) {
            $table->string('bukti_pembayaran')->nullable()->after('catatan');
        });

        // 2. LONGGARKAN enum (tambahkan rekomendasi sementara)
        DB::statement("
            ALTER TABLE penanganan 
            MODIFY hasil ENUM(
                'lunas',
                'rekom_isi_saldo',
                'rekom_tidak_isi_saldo',
                'rekomendasi',
                'tidak_ada_respon'
            ) NULL
        ");

        // 3. Update data lama → baru
        DB::statement("
            UPDATE penanganan
            SET hasil = 'rekomendasi'
            WHERE hasil IN ('rekom_isi_saldo', 'rekom_tidak_isi_saldo')
        ");

        // 4. KUNCI enum final (hapus enum lama)
        DB::statement("
            ALTER TABLE penanganan 
            MODIFY hasil ENUM(
                'lunas',
                'isi_saldo',
                'rekomendasi',
                'tidak_ada_respon'
            ) NULL
        ");
    }

    public function down(): void
    {
        // rollback enum
        DB::statement("
            ALTER TABLE penanganan 
            MODIFY hasil ENUM(
                'lunas',
                'rekom_isi_saldo',
                'rekom_tidak_isi_saldo',
                'tidak_ada_respon'
            ) NULL
        ");

        Schema::table('penanganan', function (Blueprint $table) {
            $table->dropColumn('bukti_pembayaran');
        });
    }
};
