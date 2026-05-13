<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_siswa AS
            SELECT
                person.idperson,
                person.nama,
                MAX(CASE WHEN lk.idunit NOT IN ('01', '07') THEN lk.title END)      AS unit_formal,
                MAX(CASE WHEN lk.idunit NOT IN ('01', '07') THEN lk.keterangan END) AS kelas_formal,
                MAX(CASE WHEN lk.idunit = '07' THEN lk.idtingkat END)               AS AsramaPondok,
                MAX(CASE WHEN lk.idunit = '07' THEN lk.idrombel END)                AS KamarPondok,
                MAX(CASE WHEN lk.idunit = '01' THEN lk.idtingkat END)               AS TingkatMadin,
                MAX(CASE WHEN lk.idunit = '01' THEN lk.idrombel END)                AS KelasMadin,
                COALESCE(duwit.saldo, 0) AS saldo
            FROM daruttaqwa_person.tbl_person person
            JOIN daruttaqwa_sisda.tbl_siswa sisda ON person.idperson = sisda.idperson
            JOIN v_lembaga_kelas lk ON sisda.idkelas = lk.idkelas
            LEFT JOIN duwit.person duwit ON duwit.idperson = person.idperson
            WHERE sisda.status = 1
            GROUP BY person.idperson, person.nama, duwit.saldo
        ");
    }

    public function down(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_siswa AS
            SELECT
                person.idperson,
                person.nama,
                MAX(CASE WHEN lk.idunit NOT IN ('01', '07') THEN lk.title END)      AS unit_formal,
                MAX(CASE WHEN lk.idunit NOT IN ('01', '07') THEN lk.keterangan END) AS kelas_formal,
                MAX(CASE WHEN lk.idunit = '07' THEN lk.idtingkat END)               AS AsramaPondok,
                MAX(CASE WHEN lk.idunit = '07' THEN lk.idrombel END)                AS KamarPondok,
                MAX(CASE WHEN lk.idunit = '01' THEN lk.idtingkat END)               AS TingkatMadin,
                MAX(CASE WHEN lk.idunit = '01' THEN lk.idrombel END)                AS KelasMadin,
                duwit.saldo AS saldo
            FROM daruttaqwa_person.tbl_person person
            JOIN daruttaqwa_sisda.tbl_siswa sisda ON person.idperson = sisda.idperson
            JOIN duwit.person duwit ON duwit.idperson = person.idperson
            JOIN v_lembaga_kelas lk ON sisda.idkelas = lk.idkelas
            WHERE sisda.status = 1
            GROUP BY person.idperson, person.nama, duwit.saldo
        ");
    }
};
