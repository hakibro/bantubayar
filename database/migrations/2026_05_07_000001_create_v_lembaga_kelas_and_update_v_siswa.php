<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: v_lembaga_kelas harus dibuat dulu karena v_siswa menggunakannya
        DB::statement("
            CREATE OR REPLACE VIEW v_lembaga_kelas AS
            SELECT lembaga.idunit, lembaga.title, kelas.idkelas, kelas.idtingkat, kelas.idjurusan, kelas.idrombel, kelas.keterangan
            FROM daruttaqwa_sisda.tbl_kelas kelas
            JOIN daruttaqwa_referensi.tbl_departemen lembaga ON kelas.idunit = lembaga.idunit
            WHERE kelas.idperiode = '20252026'
        ");

        // Step 2: Update v_siswa — join via v_lembaga_kelas (periode sudah terfilter di dalam view)
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
            JOIN daruttaqwa_sisda.tbl_siswa     sisda ON person.idperson = sisda.idperson
            JOIN duwit.person                   duwit ON duwit.idperson  = person.idperson
            JOIN v_lembaga_kelas                lk    ON sisda.idkelas   = lk.idkelas
            WHERE sisda.status = 1
            GROUP BY person.idperson, person.nama, duwit.saldo
        ");
    }

    public function down(): void
    {
        // Kembalikan v_siswa ke versi sebelumnya (dari migration 2026_05_06)
        DB::statement("
            CREATE OR REPLACE VIEW v_siswa AS
            SELECT
                person.idperson,
                person.nama,
                MAX(CASE WHEN lembaga.idunit NOT IN ('01', '07') THEN lembaga.title END)      AS unit_formal,
                MAX(CASE WHEN kelas.idunit   NOT IN ('01', '07') THEN kelas.keterangan END)   AS kelas_formal,
                MAX(CASE WHEN lembaga.idunit = '01' THEN kelas.idtingkat END)                 AS TingkatMadin,
                MAX(CASE WHEN kelas.idunit   = '01' THEN kelas.idrombel  END)                 AS KelasMadin,
                MAX(CASE WHEN lembaga.idunit = '07' THEN kelas.idtingkat END)                 AS AsramaPondok,
                MAX(CASE WHEN kelas.idunit   = '07' THEN kelas.idrombel  END)                 AS KamarPondok,
                duwit.saldo AS saldo
            FROM daruttaqwa_person.tbl_person person
            JOIN daruttaqwa_sisda.tbl_siswa     sisda   ON person.idperson = sisda.idperson
            JOIN duwit.person                   duwit   ON duwit.idperson  = person.idperson
            JOIN daruttaqwa_sisda.tbl_kelas     kelas   ON sisda.idkelas   = kelas.idkelas
            JOIN daruttaqwa_referensi.tbl_departemen lembaga ON kelas.idunit = lembaga.idunit
            WHERE kelas.idperiode = '20252026' AND sisda.status = 1
            GROUP BY person.idperson
        ");

        DB::statement("DROP VIEW IF EXISTS v_lembaga_kelas");
    }
};
