<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Buat View v_siswa_phone terlebih dahulu agar bisa di-join oleh v_siswa
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

        // 2. Update v_siswa dengan menambahkan kolom phone, LEFT JOIN, dan menyertakan phone.phone di GROUP BY
        DB::statement("
            CREATE OR REPLACE VIEW v_siswa AS
            SELECT 
                person.idperson, 
                person.nama,
                phone.phone,

                -- Data Formal
                MAX(CASE WHEN lembaga.idunit NOT IN ('01', '07') THEN lembaga.title END)      AS unit_formal,
                MAX(CASE WHEN kelas.idunit   NOT IN ('01', '07') THEN kelas.keterangan END)    AS kelas_formal,

                -- Data Pondok/Asrama
                MAX(CASE WHEN lembaga.idunit = '01' THEN kelas.idtingkat END)                 AS TingkatMadin,
                MAX(CASE WHEN kelas.idunit   = '01' THEN kelas.idrombel  END)                 AS KelasMadin,

                -- Data Madin
                MAX(CASE WHEN lembaga.idunit = '07' THEN kelas.idtingkat END)                 AS AsramaPondok,
                MAX(CASE WHEN kelas.idunit   = '07' THEN kelas.idrombel  END)                 AS KamarPondok,
                
                duwit.saldo AS saldo,

                -- Mengambil dari View Keuangan
                IFNULL(fin.is_lunas, 0) AS is_lunas

            FROM daruttaqwa_person.tbl_person person
            JOIN daruttaqwa_sisda.tbl_siswa     sisda   ON person.idperson = sisda.idperson
            JOIN duwit.person                   duwit   ON duwit.idperson  = person.idperson
            JOIN daruttaqwa_sisda.tbl_kelas     kelas   ON sisda.idkelas   = kelas.idkelas
            JOIN daruttaqwa_referensi.tbl_departemen lembaga ON kelas.idunit = lembaga.idunit

            -- Gabungkan dengan view phone yang baru dibuat
            LEFT JOIN v_siswa_phone phone
                ON phone.idperson = person.idperson

            -- Gabungkan dengan View Keuangan
            LEFT JOIN v_status_lunas_siswa fin 
                ON  fin.idperson  = person.idperson 
                AND fin.idperiode = '20252026'

            WHERE kelas.idperiode = '20252026' 
              AND sisda.status = 1
            
            -- Menambahkan phone.phone ke GROUP BY agar kompatibel dengan only_full_group_by
            GROUP BY person.idperson, person.nama, phone.phone
        ");
    }

    public function down(): void
    {
        // 1. Kembalikan v_siswa ke versi semula (tanpa kolom & join phone)
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

        // 2. Hapus view v_siswa_phone yang baru dibuat
        DB::statement("DROP VIEW IF EXISTS v_siswa_phone");
    }
};