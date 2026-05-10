<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
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
    }

    public function down(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_status_lunas_siswa AS
            SELECT
                idperson,
                CASE
                    WHEN COALESCE(SUM(
                        CASE
                            WHEN (jml_kredit - jml_debet) > 0
                                THEN jml_kredit - jml_debet
                            ELSE 0
                        END
                    ), 0) > 0 THEN 0
                    ELSE 1
                END AS is_lunas
            FROM daruttaqwa_trans.ips_siswa
            WHERE idperiode IN ('20212022', '20222023', '20232024', '20242025', '20252026')
              AND status = '1'
              AND tgl_jurnal < NOW()
            GROUP BY idperson
        ");
    }
};
