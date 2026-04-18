<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SiswaBelumLunasExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        // Ambil semua siswa dengan pembayaran periode < 20242025
        $query = Siswa::whereHas('pembayaran', function ($q) {
            $q->where('periode', '<', '20242025');
        })->with([
                    'pembayaran' => function ($q) {
                        $q->where('periode', '<', '20242025');
                    }
                ]);

        // Filter keyword
        if ($keyword = $this->request->get('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('idperson', 'like', "%{$keyword}%");
            });
        }
        // Filter UnitFormal
        if ($unitFormal = $this->request->get('unit_formal')) {
            $query->where('UnitFormal', $unitFormal);
        }
        // Filter AsramaPondok
        if ($asramaPondok = $this->request->get('asrama_pondok')) {
            $query->where('AsramaPondok', $asramaPondok);
        }
        // Filter TingkatDiniyah
        if ($tingkatDiniyah = $this->request->get('tingkat_diniyah')) {
            $query->where('TingkatDiniyah', $tingkatDiniyah);
        }

        $siswaList = $query->get();

        $rows = collect();

        foreach ($siswaList as $siswa) {
            // Mapping periode => kelas_info
            $kelasInfoMap = [];
            foreach ($siswa->pembayaran as $pay) {
                $periode = $pay->periode;
                if ($pay->kelas_info) {
                    $kelasInfoMap[$periode] = $pay->kelas_info;
                } elseif (isset($pay->data['kelas_info'])) {
                    $kelasInfoMap[$periode] = $pay->data['kelas_info'];
                } else {
                    $kelasInfoMap[$periode] = '-';
                }
            }

            // Loop semua pembayaran dan kategori
            foreach ($siswa->pembayaran as $pay) {
                $periode = $pay->periode;
                $data = $pay->data;
                if (empty($data['categories']))
                    continue;

                foreach ($data['categories'] as $category) {
                    $items = $category['items'] ?? [];
                    foreach ($items as $item) {
                        // Hitung sisa item = dibayar (amount_billed) - tagihan (amount_paid)
                        $tagihan = $item['amount_paid'] ?? 0;
                        $dibayar = $item['amount_billed'] ?? 0;
                        $sisa = $dibayar - $tagihan;

                        // Hanya tampilkan item yang sisa-nya tidak nol
                        if ($sisa != 0) {
                            $kelasInfo = $kelasInfoMap[$periode] ?? '-';
                            $rows->push([
                                'idperson' => $siswa->idperson,
                                'nama' => $siswa->nama,
                                'gender' => $siswa->gender,
                                'phone' => $siswa->phone,
                                'unit_formal' => $siswa->UnitFormal,
                                'kelas_formal' => $siswa->KelasFormal,
                                'asrama_pondok' => $siswa->AsramaPondok,
                                'kelas_diniyah' => $siswa->KelasDiniyah,
                                'periode' => $periode,
                                'kelas_info' => $kelasInfo,
                                'kategori' => $category['category_name'] ?? 'Unknown',
                                'item' => $item['unit_name'] ?? $item['unit_id'] ?? 'Unknown',
                                'tagihan' => $tagihan,
                                'dibayar' => $dibayar,
                                'sisa' => $sisa, // positif = kelebihan bayar, negatif = tunggakan
                            ]);
                        }
                    }
                }
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'ID Person',
            'Nama',
            'Gender',
            'Telepon',
            'Unit Formal',
            'Kelas Formal',
            'Asrama Pondok',
            'Kelas Diniyah',
            'Periode',
            'Kelas Info',
            'Kategori',
            'Item',
            'Tagihan (Rp)',
            'Dibayar (Rp)',
            'Sisa (Rp)'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'M' => '#,##0', // kolom Tagihan (index M, mulai 0: A=0, M=12)
            'N' => '#,##0', // Dibayar
            'O' => '#,##0', // Sisa
        ];
    }
}