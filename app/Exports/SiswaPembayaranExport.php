<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaPembayaranExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    /**
     * @param array<int, string> $periodes Daftar idperiode yang sudah divalidasi terhadap whitelist.
     */
    public function __construct(private Builder $query, private array $periodes)
    {
    }

    public function collection()
    {
        $periodeList = "'" . implode("','", $this->periodes) . "'";

        $totalTagihan = "COALESCE((
            SELECT SUM(iis.jml_kredit)
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperson = v_siswa.idperson
              AND iis.idperiode IN ({$periodeList})
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
        ), 0)";

        $totalBayar = "COALESCE((
            SELECT SUM(iis.jml_debet)
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperson = v_siswa.idperson
              AND iis.idperiode IN ({$periodeList})
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
        ), 0)";

        $totalSisa = "COALESCE((
            SELECT SUM(CASE WHEN (iis.jml_kredit - iis.jml_debet) > 0 THEN iis.jml_kredit - iis.jml_debet ELSE 0 END)
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperson = v_siswa.idperson
              AND iis.idperiode IN ({$periodeList})
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
        ), 0)";

        return (clone $this->query)
            ->select(
                'v_siswa.idperson',
                'v_siswa.nama',
                'v_siswa.unit_formal',
                'v_siswa.kelas_formal',
                'v_siswa.AsramaPondok',
                'v_siswa.KamarPondok',
                'v_siswa.TingkatMadin',
                'v_siswa.KelasMadin',
                DB::raw("{$totalTagihan} as export_total_tagihan"),
                DB::raw("{$totalBayar} as export_total_bayar"),
                DB::raw("{$totalSisa} as export_total_sisa"),
                DB::raw("CASE WHEN {$totalSisa} > 0 THEN 'Belum Lunas' ELSE 'Lunas' END as export_status")
            )
            ->orderBy(DB::raw($totalSisa), 'desc')
            ->get()
            ->map(fn($item) => [
                'idperson' => $item->idperson,
                'nama' => $item->nama,
                'unit_formal' => $item->unit_formal,
                'kelas_formal' => $item->kelas_formal,
                'asrama_pondok' => $item->AsramaPondok,
                'kamar_pondok' => $item->KamarPondok,
                'tingkat_madin' => $item->TingkatMadin,
                'kelas_madin' => $item->KelasMadin,
                'total_tagihan' => (int) $item->export_total_tagihan,
                'total_bayar' => (int) $item->export_total_bayar,
                'total_sisa' => (int) $item->export_total_sisa,
                'status' => $item->export_status,
            ]);
    }

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
            'Total Tagihan (Rp)',
            'Total Bayar (Rp)',
            'Total Sisa Pembayaran (Rp)',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
        ];
    }
}
