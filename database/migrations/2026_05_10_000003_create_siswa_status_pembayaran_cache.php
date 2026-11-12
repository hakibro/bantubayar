<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa_status_pembayaran', function (Blueprint $table) {
            $table->unsignedBigInteger('idperson')->primary();
            $table->decimal('total_tunggakan', 15, 2)->default(0);
            $table->boolean('is_lunas')->default(true)->index();
            $table->timestamp('refreshed_at')->nullable();
        });

        if ($this->shouldSeedCache()) {
            $this->refreshStatusPembayaran();
        }

        DB::statement("
            CREATE OR REPLACE VIEW v_status_lunas_siswa AS
            SELECT
                idperson,
                total_tunggakan,
                is_lunas
            FROM siswa_status_pembayaran
        ");
    }

    /**
     * Populasi awal cache ini membaca tabel lintas-database (daruttaqwa_trans.ips_siswa)
     * yang bisa berisi puluhan ribu baris. Saat testing, query itu tidak relevan dan
     * membuat migrasi butuh ~86 detik, sehingga dilewati. Tabel tetap dibuat kosong dan
     * diisi lewat refresh terpisah.
     */
    private function shouldSeedCache(): bool
    {
        return ! app()->environment('testing');
    }

    public function down(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_status_lunas_siswa AS
            SELECT
                iis.idperson,
                COALESCE(SUM(
                    CASE
                        WHEN (iis.jml_kredit - iis.jml_debet) > 0
                            THEN iis.jml_kredit - iis.jml_debet
                        ELSE 0
                    END
                ), 0) AS total_tunggakan,
                CASE
                    WHEN COALESCE(SUM(
                        CASE
                            WHEN (iis.jml_kredit - iis.jml_debet) > 0
                                THEN iis.jml_kredit - iis.jml_debet
                            ELSE 0
                        END
                    ), 0) > 0 THEN 0
                    ELSE 1
                END AS is_lunas
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperiode IN ('20212022', '20222023', '20232024', '20242025', '20252026')
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            GROUP BY iis.idperson
        ");

        Schema::dropIfExists('siswa_status_pembayaran');
    }

    private function refreshStatusPembayaran(): void
    {
        DB::statement("
            INSERT INTO siswa_status_pembayaran (idperson, total_tunggakan, is_lunas, refreshed_at)
            SELECT
                summary.idperson,
                summary.total_tunggakan,
                CASE WHEN summary.total_tunggakan > 0 THEN 0 ELSE 1 END AS is_lunas,
                NOW() AS refreshed_at
            FROM (
                SELECT
                    iis.idperson,
                    COALESCE(SUM(
                        CASE
                            WHEN (iis.jml_kredit - iis.jml_debet) > 0
                                THEN iis.jml_kredit - iis.jml_debet
                            ELSE 0
                        END
                    ), 0) AS total_tunggakan
                FROM daruttaqwa_trans.ips_siswa iis
                WHERE iis.idperiode IN ('20212022', '20222023', '20232024', '20242025', '20252026')
                  AND iis.status = '1'
                  AND iis.tgl_jurnal < NOW()
                GROUP BY iis.idperson
            ) summary
        ");
    }
};
