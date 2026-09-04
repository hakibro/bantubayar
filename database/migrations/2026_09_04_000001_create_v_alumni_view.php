<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_alumni AS
            SELECT
                p.idperson,
                p.nama,
                d.title            AS lembaga,
                lk.keterangan      AS kelas_lulus,
                lk.tahun           AS tahun_lulus,
                s.tgllulus         AS tgl_lulus,
                phs.phone          AS phone,
                COALESCE(dw.saldo, 0) AS saldo
            FROM daruttaqwa_sisda.tbl_siswa s
            JOIN daruttaqwa_person.tbl_person p ON p.idperson = s.idperson
            JOIN daruttaqwa_sisda.tbl_luluskel lk ON lk.idluluskel = s.idluluskel
            JOIN daruttaqwa_referensi.tbl_departemen d ON d.idunit = lk.idunit
            LEFT JOIN bantubayar.v_siswa_phone phs ON phs.idperson = p.idperson
            LEFT JOIN duwit.person dw ON dw.idperson = p.idperson
            WHERE s.alumni = 1
              AND s.idluluskel IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_alumni');
    }
};
