<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTotalTunggakanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    public function __construct(private Builder $query)
    {
    }

    public function collection()
    {
        $totalTunggakan = "COALESCE((
            SELECT status_lunas.total_tunggakan
            FROM v_status_lunas_siswa status_lunas
            WHERE status_lunas.idperson = v_siswa.idperson
            LIMIT 1
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
                DB::raw("{$totalTunggakan} as export_total_tunggakan"),
                DB::raw("CASE WHEN {$totalTunggakan} > 0 THEN 'Belum Lunas' ELSE 'Lunas' END as export_status")
            )
            ->orderBy('v_siswa.nama')
            ->get()
            ->map(fn ($item) => [
                'idperson' => $item->idperson,
                'nama' => $item->nama,
                'unit_formal' => $item->unit_formal,
                'kelas_formal' => $item->kelas_formal,
                'asrama_pondok' => $item->AsramaPondok,
                'kamar_pondok' => $item->KamarPondok,
                'tingkat_madin' => $item->TingkatMadin,
                'kelas_madin' => $item->KelasMadin,
                'total_tunggakan' => (int) $item->export_total_tunggakan,
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
            'Total Tunggakan (Rp)',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
        ];
    }
}
