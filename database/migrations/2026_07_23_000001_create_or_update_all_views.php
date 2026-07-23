<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. v_lembaga_kelas — dasar untuk v_siswa
        DB::statement("
            CREATE OR REPLACE VIEW v_lembaga_kelas AS
SELECT
    lembaga.idunit,
    lembaga.title,
    kelas.idkelas,
    kelas.idtingkat,
    kelas.idjurusan,
    kelas.idrombel,
    kelas.keterangan
FROM daruttaqwa_sisda.tbl_kelas kelas
JOIN daruttaqwa_referensi.tbl_departemen lembaga ON kelas.idunit = lembaga.idunit
WHERE kelas.idperiode = (
    SELECT idperiode 
    FROM daruttaqwa_referensi.tbl_periode 
    WHERE aktif = 1 
    LIMIT 1
)
        ");

        // 2. v_siswa_phone
        DB::statement("
            CREATE OR REPLACE VIEW v_siswa_phone AS
            SELECT
                phs.idperson,
                GROUP_CONCAT(phs.phone_number, ' - ', phs.pemilik SEPARATOR ', ') AS phone
            FROM daruttaqwa_person.tbl_person_phone phs
            WHERE phs.status = 1
              AND phs.penerima = 1
              AND phs.pemilik IN ('ayah', 'ibu')
            GROUP BY phs.idperson
        ");

        // 3. v_status_lunas_siswa — baca dari tabel cache siswa_status_pembayaran
        DB::statement("
            CREATE OR REPLACE VIEW v_status_lunas_siswa AS
            SELECT
                idperson,
                total_tunggakan,
                is_lunas
            FROM siswa_status_pembayaran
        ");

        // 4. v_siswa — gabungan semua view di atas
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
        // Kembalikan v_siswa ke versi sebelum phone & is_lunas (dari 2026_05_13_000001)
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

        // v_status_lunas_siswa kembali ke query langsung ips_siswa
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
            WHERE iis.idperiode >= '20212022'
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            GROUP BY iis.idperson
        ");

        // v_siswa_phone dan v_lembaga_kelas tidak di-drop — view ini tidak mengubah struktur,
        // hanya di-replace. down() cukup kembalikan v_siswa dan v_status_lunas_siswa.
    }
};
