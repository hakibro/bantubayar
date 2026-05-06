<?php

namespace App\Http\Controllers\Custom;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Services\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomController extends Controller
{
    public function index(Request $request)
    {
        $keyword        = $request->get('keyword');
        $unitFormal     = $request->get('unit_formal');
        $asramaPondok   = $request->get('asrama_pondok');
        $tingkatDiniyah = $request->get('tingkat_diniyah');

        $query = Siswa::where('is_lunas', false);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('idperson', 'like', "%{$keyword}%");
            });
        }
        if ($unitFormal)
            $query->where('unit_formal', $unitFormal);
        if ($asramaPondok)
            $query->where('AsramaPondok', $asramaPondok);
        if ($tingkatDiniyah)
            $query->where('TingkatMadin', $tingkatDiniyah);

        $siswaList = $query->orderBy('nama')->paginate(20)->withQueryString();

        $unitFormalList     = Siswa::whereNotNull('unit_formal')->distinct()->pluck('unit_formal');
        $asramaPondokList   = Siswa::whereNotNull('AsramaPondok')->distinct()->pluck('AsramaPondok');
        $tingkatDiniyahList = Siswa::whereNotNull('TingkatMadin')->distinct()->pluck('TingkatMadin');

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

    public function tesSiswa()
    {
        $idperson = '221207';

        try {
            $db = DB::connection('mysql_second');

            $namaSiswa = $db->table('daruttaqwa_person.tbl_person')->where('idperson', $idperson)->value('nama');
            $saldo     = $db->table('duwit.person')->where('idperson', $idperson)->value('saldo');

            $historyKelas = $db->select("
                SELECT tk.idperiode, tk.keterangan as kelas, td.title
                FROM daruttaqwa_sisda.tbl_siswa ts
                JOIN daruttaqwa_sisda.tbl_kelas tk ON tk.idkelas = ts.idkelas
                JOIN daruttaqwa_referensi.tbl_departemen td ON td.idunit = tk.idunit
                WHERE ts.idperson = ?
                GROUP BY tk.idperiode, tk.keterangan, td.title
            ", [$idperson]);

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

            $dataPeriode = [];
            foreach ($historyKelas as $h) {
                $dataPeriode[$h->idperiode]['info_kelas'] = ['kelas' => $h->kelas, 'unit' => $h->title];
            }
            foreach ($pembayaran as $p) {
                if (!isset($dataPeriode[$p->idperiode])) {
                    $dataPeriode[$p->idperiode]['info_kelas'] = ['kelas' => 'Tidak Terdata', 'unit' => '-'];
                }
                $dataPeriode[$p->idperiode]['list_pembayaran'][] = [
                    'item'       => $p->judul,
                    'tagihan'    => $p->jml_kredit,
                    'bayar'      => $p->jml_debet,
                    'lunas'      => $p->lunas,
                    'tgl_jurnal' => $p->tgl_jurnal,
                ];
            }

            return response()->json([
                'status' => 'success',
                'siswa'  => ['idperson' => $idperson, 'nama' => $namaSiswa ?? 'Tidak Ditemukan', 'saldo' => $saldo ?? 0],
                'riwayat_per_periode' => $dataPeriode,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
