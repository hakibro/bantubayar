<?php

namespace App\Http\Controllers\Custom;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\SiswaPembayaran;
use App\Exports\SiswaBelumLunasExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CustomController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $unitFormal = $request->get('unit_formal');
        $asramaPondok = $request->get('asrama_pondok');
        $tingkatDiniyah = $request->get('tingkat_diniyah');

        // Query semua siswa yang memiliki pembayaran periode < '20242025'
        $query = Siswa::whereHas('pembayaran', function ($q) {
            $q->where('periode', '<', '20242025');
        })->with([
                    'pembayaran' => function ($q) {
                        $q->where('periode', '<', '20242025');
                    }
                ]);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('idperson', 'like', "%{$keyword}%");
            });
        }
        if ($unitFormal)
            $query->where('UnitFormal', $unitFormal);
        if ($asramaPondok)
            $query->where('AsramaPondok', $asramaPondok);
        if ($tingkatDiniyah)
            $query->where('TingkatDiniyah', $tingkatDiniyah);

        // Ambil semua data (belum dipaginasi)
        $allSiswa = $query->get();

        // Filter hanya siswa yang memiliki tunggakan atau kelebihan bayar
        $filteredSiswa = $allSiswa->filter(function ($siswa) {
            return !empty($siswa->getKategoriSaldoTidakNol());
        });

        // Paginasi manual
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentItems = $filteredSiswa->slice(($page - 1) * $perPage, $perPage)->values();
        $siswaList = new LengthAwarePaginator($currentItems, $filteredSiswa->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        // Data untuk dropdown filter
        $unitFormalList = Siswa::whereNotNull('UnitFormal')->distinct()->pluck('UnitFormal');
        $asramaPondokList = Siswa::whereNotNull('AsramaPondok')->distinct()->pluck('AsramaPondok');
        $tingkatDiniyahList = Siswa::whereNotNull('TingkatDiniyah')->distinct()->pluck('TingkatDiniyah');

        // Proses grouping saldo tidak nol per periode untuk setiap siswa
        foreach ($siswaList as $siswa) {
            $kategoriTidakNol = $siswa->getKategoriSaldoTidakNol();
            $grouped = [];
            foreach ($kategoriTidakNol as $item) {
                $periode = $item['periode'];
                if (!isset($grouped[$periode])) {
                    $grouped[$periode] = [];
                }
                $grouped[$periode][] = $item;
            }
            $siswa->tunggakan_per_periode = $grouped;
            $siswa->total_tunggakan = $siswa->getTotalTunggakan();
            $siswa->total_overpaid = $siswa->getTotalOverpaid();
        }

        return view('custom.index', compact(
            'siswaList',
            'keyword',
            'unitFormal',
            'asramaPondok',
            'tingkatDiniyah',
            'unitFormalList',
            'asramaPondokList',
            'tingkatDiniyahList'
        ));
    }

    public function export(Request $request)
    {
        return Excel::download(new SiswaBelumLunasExport($request), 'siswa_belum_lunas_' . date('Y-m-d_His') . '.xlsx');
    }

    public function tesSiswa()
    {
        // try {
        //     DB::connection('mysql_second')->getPdo();
        //     return "Koneksi Berhasil!";
        // } catch (\Exception $e) {
        //     return "Gagal Koneksi: " . $e->getMessage();
        // }


        $idperson = '221207';

        try {
            $db = DB::connection('mysql_second');

            // 1. Ambil Data Dasar (Nama & Saldo)
            $namaSiswa = $db->table('daruttaqwa_person.tbl_person')->where('idperson', $idperson)->value('nama');
            $saldo = $db->table('duwit.person')->where('idperson', $idperson)->value('saldo');

            // 2. Ambil Histori Kelas
            $historyKelas = $db->select("
                SELECT tk.idperiode, tk.keterangan as kelas, td.title
                FROM daruttaqwa_sisda.tbl_siswa ts 
                JOIN daruttaqwa_sisda.tbl_kelas tk ON tk.idkelas = ts.idkelas 
                JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = tk.idunit 
                WHERE ts.idperson = ? 
                GROUP BY tk.idperiode, tk.keterangan, td.title
            ", [$idperson]);

            // 3. Ambil Data Pembayaran (Filter periode dihapus agar mengambil semua)
            $pembayaran = $db->select("
                SELECT iis.idperiode, iis.jml_kredit, iis.jml_debet, iis.lunas, iis.tgl_jurnal, tim.judul
                FROM daruttaqwa_trans.ips_siswa iis 
                JOIN daruttaqwa_trans.tbl_ips_unit tiu ON tiu.ipsunit = iis.ipsunit 
                JOIN daruttaqwa_trans.tbl_ips_main tim ON tim.ipsmain = tiu.ipsmain
                JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = iis.idunit  
                WHERE iis.idperson = ? 
                AND iis.status = '1'
                ORDER BY iis.idperiode DESC, iis.tgl_jurnal ASC
            ", [$idperson]);

            // --- PROSES PENGGABUNGAN DATA BERDASARKAN PERIODE ---

            $dataPeriode = [];

            // Masukkan data kelas ke dalam grup periode
            foreach ($historyKelas as $h) {
                $dataPeriode[$h->idperiode]['info_kelas'] = [
                    'kelas' => $h->kelas,
                    'unit' => $h->title
                ];
            }

            // Masukkan data pembayaran ke dalam grup periode
            foreach ($pembayaran as $p) {
                // Jika info_kelas belum ada (misal ada transaksi di periode lama tapi data kelas tidak ada)
                // kita inisialisasi agar tidak error
                if (!isset($dataPeriode[$p->idperiode])) {
                    $dataPeriode[$p->idperiode]['info_kelas'] = [
                        'kelas' => 'Tidak Terdata',
                        'unit' => '-'
                    ];
                }

                $dataPeriode[$p->idperiode]['list_pembayaran'][] = [
                    'item' => $p->judul,
                    'kategori' => $p->judul,
                    'tagihan' => $p->jml_kredit,
                    'bayar' => $p->jml_debet,
                    'lunas' => $p->lunas,
                    'tgl_jurnal' => $p->tgl_jurnal
                ];
            }

            // Response Akhir
            return response()->json([
                'status' => 'success',
                'siswa' => [
                    'idperson' => $idperson,
                    'nama' => $namaSiswa ?? 'Tidak Ditemukan',
                    'saldo' => $saldo ?? 0,
                ],
                'riwayat_per_periode' => $dataPeriode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}