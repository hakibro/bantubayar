<?php

namespace App\Exports;

use App\Models\Alumni;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class SiswaHistoryPembayaranExport
{
    /**
     * @param array<int, string> $periodes Daftar idperiode yang sudah divalidasi terhadap whitelist.
     * @param string $jenis 'siswa' (hanya siswa aktif) atau 'alumni' (hanya alumni).
     */
    public function __construct(private array $periodes, private string $jenis = 'siswa')
    {
        // Selalu minimal punya satu periode agar IN () tidak kosong.
        if (empty($this->periodes)) {
            $this->periodes = ['-'];
        }

        if (!in_array($this->jenis, ['siswa', 'alumni'], true)) {
            $this->jenis = 'siswa';
        }
    }

    /**
     * Streaming data via cursor agar hemat memori (bisa ratusan ribu baris).
     */
    public function generator(): \Generator
    {
        // Hanya bangun peta data yang diminta (siswa ATAU alumni) agar hemat memori.
        $siswa = collect();
        $alumni = collect();

        if ($this->jenis === 'alumni') {
            $alumni = Alumni::query()
                ->select('idperson', 'nama', 'lembaga', 'kelas_lulus', 'tahun_lulus')
                ->get()
                ->keyBy(fn($item) => (string) $item->idperson);
        } else {
            $siswa = Siswa::query()
                ->select('idperson', 'nama', 'unit_formal', 'kelas_formal', 'AsramaPondok', 'KamarPondok', 'TingkatMadin', 'KelasMadin')
                ->get()
                ->keyBy(fn($item) => (string) $item->idperson);
        }

        $placeholders = implode(',', array_fill(0, count($this->periodes), '?'));

        $sql = "
            SELECT
                iis.idperson,
                iis.idperiode,
                iis.idunit,
                td.title AS nama_unit,
                GROUP_CONCAT(DISTINCT tim.judul ORDER BY tim.judul SEPARATOR ' | ') AS kategori,
                SUM(iis.jml_kredit) AS tagihan,
                SUM(iis.jml_debet) AS dibayar
            FROM daruttaqwa_trans.ips_siswa iis USE INDEX (idperiode)
            JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = iis.idunit
            JOIN daruttaqwa_trans.tbl_ips_unit tiu ON tiu.ipsunit = iis.ipsunit
            JOIN daruttaqwa_trans.tbl_ips_main tim ON tim.ipsmain = tiu.ipsmain
            WHERE iis.status = '1'
              AND iis.tgl_jurnal < NOW()
              AND iis.idperiode IN ({$placeholders})
            GROUP BY iis.idperson, iis.idperiode, iis.idunit, td.title
            ORDER BY iis.idperiode DESC, iis.idperson, td.title
        ";

        foreach (DB::cursor($sql, $this->periodes) as $row) {
            $id = (string) $row->idperson;

            if ($this->jenis === 'alumni') {
                $dataAlumni = $alumni->get($id);
                if (!$dataAlumni) {
                    continue;
                }
                $dataSiswa = null;
            } else {
                $dataSiswa = $siswa->get($id);
                if (!$dataSiswa) {
                    continue;
                }
                $dataAlumni = null;
            }

            $tagihan = (int) $row->tagihan;
            $dibayar = (int) $row->dibayar;
            $sisa = $tagihan - $dibayar;

            yield [
                (string) $row->idperson,
                $dataSiswa?->nama ?? $dataAlumni?->nama,
                $dataSiswa?->unit_formal ?? $dataAlumni?->lembaga,
                $dataSiswa?->kelas_formal ?? $dataAlumni?->kelas_lulus,
                $dataSiswa?->AsramaPondok,
                $dataSiswa?->KamarPondok,
                $dataSiswa?->TingkatMadin,
                $dataSiswa?->KelasMadin,
                (string) $row->idperiode,
                $row->nama_unit,
                $row->kategori,
                $tagihan,
                $dibayar,
                $sisa,
                $sisa > 0 ? 'Belum Lunas' : 'Lunas',
            ];
        }
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID Person',
            'Nama',
            'Unit Formal',
            'Kelas Formal',
            'Asrama Pondok',
            'Kamar Pondok',
            'Tingkat Madin',
            'Kelas Madin',
            'Periode',
            'Unit',
            'Kategori',
            'Tagihan (Rp)',
            'Dibayar (Rp)',
            'Sisa (Rp)',
            'Status',
        ];
    }
}
