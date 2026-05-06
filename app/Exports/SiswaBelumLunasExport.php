<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Services\PembayaranService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class SiswaBelumLunasExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        /** @var PembayaranService $service */
        $service = app(PembayaranService::class);

        $query = Siswa::query()
            ->join('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_siswa.idperson')
            ->select('v_siswa.*')
            ->where('sl.is_lunas', 0);

        if ($keyword = $this->request->get('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('idperson', 'like', "%{$keyword}%");
            });
        }
        if ($unitFormal = $this->request->get('unit_formal')) {
            $query->where('unit_formal', $unitFormal);
        }
        if ($asramaPondok = $this->request->get('asrama_pondok')) {
            $query->where('AsramaPondok', $asramaPondok);
        }
        if ($tingkatDiniyah = $this->request->get('tingkat_diniyah')) {
            $query->where('TingkatMadin', $tingkatDiniyah);
        }

        $siswaList = $query->get();
        $rows = [];

        foreach ($siswaList as $siswa) {
            $belumLunas = $service->getDetailBelumLunas((string) $siswa->idperson);

            foreach ($belumLunas as $item) {
                $rows[] = [
                    'idperson'      => $siswa->idperson,
                    'nama'          => $siswa->nama,
                    'unit_formal'   => $siswa->unit_formal,
                    'kelas_formal'  => $siswa->kelas_formal,
                    'asrama_pondok' => $siswa->AsramaPondok,
                    'kamar'         => $siswa->KamarPondok,
                    'periode'       => $item->idperiode,
                    'kategori'      => $item->judul,
                    'unit'          => $item->nama_unit,
                    'tagihan'       => $item->jml_kredit,
                    'dibayar'       => $item->jml_debet,
                    'sisa'          => $item->selisih,
                ];
            }
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'ID Person',
            'Nama',
            'Unit Formal',
            'Kelas Formal',
            'Asrama Pondok',
            'Kamar',
            'Periode',
            'Kategori',
            'Unit',
            'Tagihan (Rp)',
            'Dibayar (Rp)',
            'Sisa (Rp)',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
        ];
    }
}
