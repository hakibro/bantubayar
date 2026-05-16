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
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT lembaga_history.title ORDER BY lembaga_history.title SEPARATOR ' | ')
                    FROM daruttaqwa_sisda.tbl_siswa siswa_history
                    JOIN daruttaqwa_sisda.tbl_kelas kelas_history ON kelas_history.idkelas = siswa_history.idkelas
                    JOIN daruttaqwa_referensi.tbl_departemen lembaga_history ON lembaga_history.idunit = kelas_history.idunit
                    WHERE siswa_history.idperson = iis.idperson
                      AND siswa_history.status = 1
                      AND kelas_history.idperiode = iis.idperiode
                      AND kelas_history.idunit NOT IN ('01', '07')
                ) as periode_unit_formal"),
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT kelas_history.keterangan ORDER BY kelas_history.keterangan SEPARATOR ' | ')
                    FROM daruttaqwa_sisda.tbl_siswa siswa_history
                    JOIN daruttaqwa_sisda.tbl_kelas kelas_history ON kelas_history.idkelas = siswa_history.idkelas
                    WHERE siswa_history.idperson = iis.idperson
                      AND siswa_history.status = 1
                      AND kelas_history.idperiode = iis.idperiode
                      AND kelas_history.idunit NOT IN ('01', '07')
                ) as periode_kelas_formal"),
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT kelas_history.idtingkat ORDER BY kelas_history.idtingkat SEPARATOR ' | ')
                    FROM daruttaqwa_sisda.tbl_siswa siswa_history
                    JOIN daruttaqwa_sisda.tbl_kelas kelas_history ON kelas_history.idkelas = siswa_history.idkelas
                    WHERE siswa_history.idperson = iis.idperson
                      AND siswa_history.status = 1
                      AND kelas_history.idperiode = iis.idperiode
                      AND kelas_history.idunit = '07'
                ) as periode_asrama_pondok"),
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT kelas_history.idrombel ORDER BY kelas_history.idrombel SEPARATOR ' | ')
                    FROM daruttaqwa_sisda.tbl_siswa siswa_history
                    JOIN daruttaqwa_sisda.tbl_kelas kelas_history ON kelas_history.idkelas = siswa_history.idkelas
                    WHERE siswa_history.idperson = iis.idperson
                      AND siswa_history.status = 1
                      AND kelas_history.idperiode = iis.idperiode
                      AND kelas_history.idunit = '07'
                ) as periode_kamar_pondok"),
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT kelas_history.idtingkat ORDER BY kelas_history.idtingkat SEPARATOR ' | ')
                    FROM daruttaqwa_sisda.tbl_siswa siswa_history
                    JOIN daruttaqwa_sisda.tbl_kelas kelas_history ON kelas_history.idkelas = siswa_history.idkelas
                    WHERE siswa_history.idperson = iis.idperson
                      AND siswa_history.status = 1
                      AND kelas_history.idperiode = iis.idperiode
                      AND kelas_history.idunit = '01'
                ) as periode_tingkat_madin"),
                DB::raw("(
                    SELECT GROUP_CONCAT(DISTINCT kelas_history.idrombel ORDER BY kelas_history.idrombel SEPARATOR ' | ')
                    FROM daruttaqwa_sisda.tbl_siswa siswa_history
                    JOIN daruttaqwa_sisda.tbl_kelas kelas_history ON kelas_history.idkelas = siswa_history.idkelas
                    WHERE siswa_history.idperson = iis.idperson
                      AND siswa_history.status = 1
                      AND kelas_history.idperiode = iis.idperiode
                      AND kelas_history.idunit = '01'
                ) as periode_kelas_madin"),
                DB::raw('(iis.jml_kredit - iis.jml_debet) as selisih')
            )
            ->whereBetween('iis.idperiode', ['20212022', '20232024'])
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
                    'idperson' => $item->idperson,
                    'nama' => $item->nama,
                    'unit_formal' => $item->unit_formal,
                    'kelas_formal' => $item->kelas_formal,
                    'asrama_pondok' => $item->AsramaPondok,
                    'kamar' => $item->KamarPondok,
                    'tingkat_madin' => $item->TingkatMadin,
                    'kelas_madin' => $item->KelasMadin,
                    'periode' => $item->idperiode,
                    'periode_unit_formal' => $item->periode_unit_formal,
                    'periode_kelas_formal' => $item->periode_kelas_formal,
                    'periode_asrama_pondok' => $item->periode_asrama_pondok,
                    'periode_kamar_pondok' => $item->periode_kamar_pondok,
                    'periode_tingkat_madin' => $item->periode_tingkat_madin,
                    'periode_kelas_madin' => $item->periode_kelas_madin,
                    'unit' => $item->nama_unit,
                    'kategori' => $item->judul,
                    'tagihan' => $item->jml_kredit,
                    'dibayar' => $item->jml_debet,
                    'tunggakan' => max((int) $item->selisih, 0),
                    'kelebihan' => abs(min((int) $item->selisih, 0)),
                    'status' => $item->selisih > 0 ? 'Belum Lunas' : 'Kelebihan Bayar',
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
            'Periode Unit Formal',
            'Periode Kelas Formal',
            'Periode Asrama Pondok',
            'Periode Kamar Pondok',
            'Periode Tingkat Madin',
            'Periode Kelas Madin',
            'Unit',
            'Kategori',
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
            'R' => '#,##0',
            'S' => '#,##0',
            'T' => '#,##0',
            'U' => '#,##0',
        ];
    }
}
