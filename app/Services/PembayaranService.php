<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PembayaranService
{
    const PERIODES = ['20212022', '20222023', '20232024', '20242025', '20252026'];

    /**
     * Seluruh tagihan siswa (lunas maupun belum) lintas periode.
     */
    public function getDetailPembayaran(string $idperson, array $periodes = null): array
    {
        $periodes = $periodes ?? self::PERIODES;
        $placeholders = implode(',', array_fill(0, count($periodes), '?'));

        return DB::select("
            SELECT
                iis.idperson, iis.idperiode,
                tim.judul,
                td.title AS nama_unit,
                iis.jml_kredit, iis.jml_debet,
                (iis.jml_kredit - iis.jml_debet) AS selisih,
                iis.lunas, iis.tgl_jurnal, iis.tgl_update
            FROM daruttaqwa_trans.ips_siswa iis
            JOIN daruttaqwa_trans.tbl_ips_unit tiu ON tiu.ipsunit = iis.ipsunit
            JOIN daruttaqwa_trans.tbl_ips_main tim ON tim.ipsmain = tiu.ipsmain
            JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = iis.idunit
            WHERE iis.idperson = ?
              AND iis.idperiode IN ({$placeholders})
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            ORDER BY lunas DESC, idperiode DESC, tgl_jurnal, judul ASC
        ", array_merge([$idperson], $periodes));
    }

    /**
     * Hanya item yang belum lunas (iis.lunas = 0).
     */
    public function getDetailBelumLunas(string $idperson, array $periodes = null): array
    {
        $periodes = $periodes ?? self::PERIODES;
        $placeholders = implode(',', array_fill(0, count($periodes), '?'));

        return DB::select("
            SELECT
                iis.idperson, iis.idperiode,
                tim.judul,
                td.title AS nama_unit,
                iis.jml_kredit, iis.jml_debet,
                (iis.jml_kredit - iis.jml_debet) AS selisih,
                iis.lunas, iis.tgl_jurnal, iis.tgl_update
            FROM daruttaqwa_trans.ips_siswa iis
            JOIN daruttaqwa_trans.tbl_ips_unit tiu ON tiu.ipsunit = iis.ipsunit
            JOIN daruttaqwa_trans.tbl_ips_main tim ON tim.ipsmain = tiu.ipsmain
            JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = iis.idunit
            WHERE iis.idperson = ?
              AND iis.idperiode IN ({$placeholders})
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
              AND iis.lunas = 0
            ORDER BY lunas DESC, idperiode DESC, tgl_jurnal, judul ASC
        ", array_merge([$idperson], $periodes));
    }

    /**
     * Ringkasan total kredit/debet per periode.
     */
    public function getSummaryPerPeriode(string $idperson, array $periodes = null): array
    {
        $periodes = $periodes ?? self::PERIODES;
        $placeholders = implode(',', array_fill(0, count($periodes), '?'));

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
              AND iis.idperiode IN ({$placeholders})
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            GROUP BY person.idperson, person.nama, iis.idperiode
            ORDER BY iis.idperiode ASC
        ", array_merge([$idperson], $periodes));
    }

    /**
     * Total rupiah kurang bayar siswa saat ini.
     */
    public function getTotalBelumLunas(string $idperson, array $periodes = null): int
    {
        return (int) collect($this->getDetailBelumLunas($idperson, $periodes))
            ->sum('selisih');
    }
}