<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AlumniTotalTunggakanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    public function __construct(private Builder $query)
    {
    }

    public function collection()
    {
        return (clone $this->query)
            ->orderBy('v_alumni.nama')
            ->get()
            ->map(fn($item) => [
                'idperson' => $item->idperson,
                'nama' => $item->nama,
                'lembaga' => $item->lembaga ?? '-',
                'kelas_lulus' => $item->kelas_lulus ?? '-',
                'tahun_lulus' => $item->tahun_lulus ?? '-',
                'phone' => $item->phone ?? '-',
                'total_tunggakan' => (int) ($item->total_tunggakan ?? 0),
                'status' => (int) ($item->total_tunggakan ?? 0) > 0 ? 'Belum Lunas' : 'Lunas',
            ]);
    }

    public function headings(): array
    {
        return [
            'ID Person',
            'Nama',
            'Lembaga',
            'Kelas Lulus',
            'Tahun Lulus',
            'Telepon',
            'Total Tunggakan (Rp)',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
        ];
    }
}
