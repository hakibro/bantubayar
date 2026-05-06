<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // View 1: Status lunas per siswa per periode
        DB::statement("
            CREATE OR REPLACE VIEW v_status_lunas_siswa AS
            SELECT
                idperson,
                idperiode,
                IF(MIN(lunas) = 1, 1, 0) AS is_lunas
            FROM daruttaqwa_trans.ips_siswa
            GROUP BY idperson, idperiode
        ");

        // View 2: Data siswa lengkap dari lintas database
        DB::statement("
            CREATE OR REPLACE VIEW v_siswa AS
            SELECT
                person.idperson,
                person.nama,

                -- Data Formal
                MAX(CASE WHEN lembaga.idunit NOT IN ('01', '07') THEN lembaga.title END)       AS unit_formal,
                MAX(CASE WHEN kelas.idunit  NOT IN ('01', '07') THEN kelas.keterangan END)     AS kelas_formal,

                -- Data Pondok/Asrama
                MAX(CASE WHEN lembaga.idunit = '01' THEN kelas.idtingkat END)                  AS TingkatMadin,
                MAX(CASE WHEN kelas.idunit   = '01' THEN kelas.idrombel  END)                  AS KelasMadin,

                -- Data Madin
                MAX(CASE WHEN lembaga.idunit = '07' THEN kelas.idtingkat END)                  AS AsramaPondok,
                MAX(CASE WHEN kelas.idunit   = '07' THEN kelas.idrombel  END)                  AS KamarPondok,

                duwit.saldo                    AS saldo,
                IFNULL(fin.is_lunas, 0)        AS is_lunas

            FROM daruttaqwa_person.tbl_person person
            JOIN daruttaqwa_sisda.tbl_siswa     sisda  ON person.idperson  = sisda.idperson
            JOIN duwit.person                   duwit  ON duwit.idperson   = person.idperson
            JOIN daruttaqwa_sisda.tbl_kelas     kelas  ON sisda.idkelas    = kelas.idkelas
            JOIN daruttaqwa_referensi.tbl_departemen lembaga ON kelas.idunit = lembaga.idunit

            LEFT JOIN v_status_lunas_siswa fin
                ON  fin.idperson  = person.idperson
                AND fin.idperiode = '20252026'

            WHERE kelas.idperiode = '20252026'
              AND sisda.status    = 1
            GROUP BY person.idperson, person.nama
        ");

        // Index pada daruttaqwa_sisda.tbl_siswa
        try {
            DB::statement("
                ALTER TABLE daruttaqwa_sisda.tbl_siswa
                ADD INDEX idx_status_idperson (status, idperson)
            ");
        } catch (\Exception $e) {
            // Index sudah ada, lewati
        }

        // Index pada daruttaqwa_trans.ips_siswa
        try {
            DB::statement("
                ALTER TABLE daruttaqwa_trans.ips_siswa
                ADD INDEX idx_idperson_idperiode_lunas (idperson, idperiode, lunas)
            ");
        } catch (\Exception $e) {
            // Index sudah ada, lewati
        }

        // Index pada duwit.person
        try {
            DB::statement("
                ALTER TABLE duwit.person
                ADD INDEX idx_idperson_saldo (idperson, saldo)
            ");
        } catch (\Exception $e) {
            // Index sudah ada, lewati
        }
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_siswa");
        DB::statement("DROP VIEW IF EXISTS v_status_lunas_siswa");

        try {
            DB::statement("ALTER TABLE daruttaqwa_sisda.tbl_siswa DROP INDEX idx_status_idperson");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE daruttaqwa_trans.ips_siswa DROP INDEX idx_idperson_idperiode_lunas");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE duwit.person DROP INDEX idx_idperson_saldo");
        } catch (\Exception $e) {}
    }
};
