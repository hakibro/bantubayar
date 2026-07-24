<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PembayaranService
{
    public function refreshStatusLunasSiswa(string $idperson): void
    {
        $summary = DB::selectOne("
            SELECT
                ? AS idperson,
                COALESCE(SUM(
                    CASE
                        WHEN (iis.jml_kredit - iis.jml_debet) > 0
                            THEN iis.jml_kredit - iis.jml_debet
                        ELSE 0
                    END
                ), 0) AS total_tunggakan
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperson = ?
              AND iis.idperiode >= '20212022'
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
        ", [$idperson, $idperson]);

        $totalTunggakan = (float) ($summary->total_tunggakan ?? 0);

        DB::table('siswa_status_pembayaran')->updateOrInsert(
            ['idperson' => $idperson],
            [
                'total_tunggakan' => $totalTunggakan,
                'is_lunas' => $totalTunggakan > 0 ? 0 : 1,
                'refreshed_at' => now(),
            ]
        );
    }

    public function refreshStatusLunasSemuaSiswa(): int
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tmp_siswa_status_pembayaran');

        DB::statement("
            CREATE TEMPORARY TABLE tmp_siswa_status_pembayaran AS
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
                WHERE iis.idperiode >= '20212022'
                  AND iis.status = '1'
                  AND iis.tgl_jurnal < NOW()
                GROUP BY iis.idperson
            ) summary
        ");

        DB::transaction(function () {
            DB::table('siswa_status_pembayaran')->delete();
            DB::statement("
                INSERT INTO siswa_status_pembayaran (idperson, total_tunggakan, is_lunas, refreshed_at)
                SELECT idperson, total_tunggakan, is_lunas, refreshed_at
                FROM tmp_siswa_status_pembayaran
            ");
        });

        return DB::table('siswa_status_pembayaran')->count();
    }

    /**
     * Seluruh tagihan siswa (lunas maupun belum) lintas periode.
     */
    public function getDetailPembayaran(string $idperson): array
    {
        return DB::select("
            SELECT
                iis.idperson, iis.idperiode,
                tim.judul,
                td.title AS nama_unit,
                iis.jml_kredit, iis.jml_debet,
                (iis.jml_kredit - iis.jml_debet) AS selisih,
                CASE
                    WHEN (iis.jml_kredit - iis.jml_debet) > 0 THEN 0
                    ELSE 1
                END AS lunas,
                iis.tgl_jurnal, iis.tgl_update
            FROM daruttaqwa_trans.ips_siswa iis
            JOIN daruttaqwa_trans.tbl_ips_unit tiu ON tiu.ipsunit = iis.ipsunit
            JOIN daruttaqwa_trans.tbl_ips_main tim ON tim.ipsmain = tiu.ipsmain
            JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = iis.idunit
            WHERE iis.idperson = ?
              AND iis.idperiode >= '20212022'
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            ORDER BY lunas DESC, idperiode DESC, tgl_jurnal, judul ASC
        ", [$idperson]);
    }

    /**
     * Hanya item yang masih punya sisa tagihan.
     */
    public function getDetailBelumLunas(string $idperson): array
    {
        return DB::select("
            SELECT
                iis.idperson, iis.idperiode,
                tim.judul,
                td.title AS nama_unit,
                iis.jml_kredit, iis.jml_debet,
                (iis.jml_kredit - iis.jml_debet) AS selisih,
                CASE
                    WHEN (iis.jml_kredit - iis.jml_debet) > 0 THEN 0
                    ELSE 1
                END AS lunas,
                iis.tgl_jurnal, iis.tgl_update
            FROM daruttaqwa_trans.ips_siswa iis
            JOIN daruttaqwa_trans.tbl_ips_unit tiu ON tiu.ipsunit = iis.ipsunit
            JOIN daruttaqwa_trans.tbl_ips_main tim ON tim.ipsmain = tiu.ipsmain
            JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = iis.idunit
            WHERE iis.idperson = ?
              AND iis.idperiode >= '20212022'
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
              AND (iis.jml_kredit - iis.jml_debet) > 0
            ORDER BY lunas DESC, idperiode DESC, tgl_jurnal, judul ASC
        ", [$idperson]);
    }

    /**
     * Ringkasan total kredit/debet per periode.
     */
    public function getSummaryPerPeriode(string $idperson): array
    {
        return DB::select("
            SELECT
                person.idperson,
                person.nama,
                (SELECT GROUP_CONCAT(l.title, ' - ', k.keterangan SEPARATOR ' | ')
                 FROM daruttaqwa_sisda.tbl_siswa s
                 JOIN daruttaqwa_sisda.tbl_kelas k ON s.idkelas = k.idkelas
                 JOIN daruttaqwa_referensi.tbl_departemen l ON k.idunit = l.idunit
                 WHERE s.idperson = person.idperson
                   AND k.idperiode = iis.idperiode
                   AND s.status = 1) AS kelas_history,
                iis.idperiode,
                SUM(iis.jml_kredit)                          AS total_kredit,
                SUM(iis.jml_debet)                           AS total_debet,
                (SUM(iis.jml_kredit) - SUM(iis.jml_debet))  AS sisa_tagihan,
                CASE
                    WHEN SUM(iis.jml_debet) >= SUM(iis.jml_kredit) THEN 1
                    ELSE 0
                END AS lunas
            FROM daruttaqwa_trans.ips_siswa iis
            JOIN daruttaqwa_person.tbl_person person ON person.idperson = iis.idperson
            WHERE iis.idperson = ?
              AND iis.idperiode >= '20212022'
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            GROUP BY person.idperson, person.nama, iis.idperiode
            ORDER BY iis.idperiode ASC
        ", [$idperson]);
    }

    /**
     * Total rupiah kurang bayar siswa saat ini.
     */
    public function getTotalBelumLunas(string $idperson): int
    {
        $summary = DB::selectOne("
            SELECT
                COALESCE(SUM(
                    CASE
                        WHEN (iis.jml_kredit - iis.jml_debet) > 0
                            THEN iis.jml_kredit - iis.jml_debet
                        ELSE 0
                    END
                ), 0) AS total_tunggakan
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperson = ?
              AND iis.idperiode >= '20212022'
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
        ", [$idperson]);

        return (int) ($summary->total_tunggakan ?? 0);
    }
}
