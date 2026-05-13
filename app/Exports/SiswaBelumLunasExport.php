<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $jenis = $this->request->get('jenis', 'semua');

        $query = Siswa::query()
            ->join('daruttaqwa_trans.ips_siswa as iis', 'iis.idperson', '=', 'v_siswa.idperson')
            ->join('daruttaqwa_trans.tbl_ips_unit as tiu', 'tiu.ipsunit', '=', 'iis.ipsunit')
            ->join('daruttaqwa_trans.tbl_ips_main as tim', 'tim.ipsmain', '=', 'tiu.ipsmain')
            ->join('daruttaqwa_referensi.tbl_departemen as td', 'td.idunit', '=', 'iis.idunit')
            ->select(
                'v_siswa.idperson',
                'v_siswa.nama',
                'v_siswa.unit_formal',
                'v_siswa.kelas_formal',
                'v_siswa.AsramaPondok',
                'v_siswa.KamarPondok',
                'v_siswa.TingkatMadin',
                'v_siswa.KelasMadin',
                'iis.idperiode',
                'tim.judul',
                'td.title as nama_unit',
                'iis.jml_kredit',
                'iis.jml_debet',
                DB::raw('(iis.jml_kredit - iis.jml_debet) as selisih')
            )
            ->where('iis.idperiode', '<', '20242025')
            ->where('iis.status', '1')
            ->whereRaw('iis.tgl_jurnal < NOW()')
            ->whereRaw('(iis.jml_kredit - iis.jml_debet) != 0');

        if ($jenis === 'tunggakan') {
            $query->whereRaw('(iis.jml_kredit - iis.jml_debet) > 0');
        } elseif ($jenis === 'kelebihan') {
            $query->whereRaw('(iis.jml_kredit - iis.jml_debet) < 0');
        }

        if ($keyword = $this->request->get('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('v_siswa.nama', 'like', "%{$keyword}%")
                    ->orWhere('v_siswa.idperson', 'like', "%{$keyword}%");
            });
        }
        if ($unitFormal = $this->request->get('unit_formal')) {
            $query->where('v_siswa.unit_formal', $unitFormal);
        }
        if ($asramaPondok = $this->request->get('asrama_pondok')) {
            $query->where('v_siswa.AsramaPondok', $asramaPondok);
        }
        if ($tingkatDiniyah = $this->request->get('tingkat_diniyah')) {
            $query->where('v_siswa.TingkatMadin', $tingkatDiniyah);
        }

        return $query
            ->orderBy('v_siswa.nama')
            ->orderByDesc('iis.idperiode')
            ->get()
            ->map(function ($item) {
                return [
                    'idperson'       => $item->idperson,
                    'nama'           => $item->nama,
                    'unit_formal'    => $item->unit_formal,
                    'kelas_formal'   => $item->kelas_formal,
                    'asrama_pondok'  => $item->AsramaPondok,
                    'kamar'          => $item->KamarPondok,
                    'tingkat_madin'  => $item->TingkatMadin,
                    'kelas_madin'    => $item->KelasMadin,
                    'periode'        => $item->idperiode,
                    'kategori'       => $item->judul,
                    'unit'           => $item->nama_unit,
                    'tagihan'        => $item->jml_kredit,
                    'dibayar'        => $item->jml_debet,
                    'tunggakan'      => max((int) $item->selisih, 0),
                    'kelebihan'      => abs(min((int) $item->selisih, 0)),
                    'status'         => $item->selisih > 0 ? 'Belum Lunas' : 'Kelebihan Bayar',
                ];
            });
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
            'Tingkat Madin',
            'Kelas Madin',
            'Periode',
            'Kategori',
            'Unit',
            'Tagihan (Rp)',
            'Dibayar (Rp)',
            'Tunggakan (Rp)',
            'Kelebihan Bayar (Rp)',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0',
            'O' => '#,##0',
        ];
    }
}
