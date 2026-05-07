<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\LembagaKelas;
use App\Models\Siswa;
use App\Services\PembayaranService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $lembagaUser = auth()->user()->lembaga;
        $scope       = Siswa::query()
            ->leftJoin('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_siswa.idperson')
            ->select('v_siswa.*', 'sl.is_lunas');

        // Scope berdasarkan Role
        if (Auth::user()->hasRole('petugas')) {
            $scope->whereHas('petugas', function ($q) {
                $q->where('users.id', Auth::id());
            });
        } else {
            $scope->where(function ($q) use ($lembagaUser) {
                $q->where('unit_formal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatMadin', $lembagaUser);
            });
        }

        // Kolom v_siswa yang dipakai untuk filter pada query utama
        $siswaCols     = ['unit_formal', 'kelas_formal', 'AsramaPondok', 'KamarPondok', 'TingkatMadin', 'KelasMadin'];
        $filterOptions = [];

        if (!$request->ajax()) {
            // Ambil opsi filter langsung dari v_lembaga_kelas — lebih cepat dari scan v_siswa
            $filterOptions['unit_formal']       = LembagaKelas::formal()->distinct()->orderBy('title')->pluck('title');
            $filterOptions['kelas_formal']      = LembagaKelas::formal()->distinct()->orderBy('keterangan')->pluck('keterangan');
            $filterOptions['AsramaPondok']      = LembagaKelas::asrama()->distinct()->orderBy('idtingkat')->pluck('idtingkat');
            $filterOptions['KamarPondok']       = LembagaKelas::asrama()->distinct()->orderBy('idrombel')->pluck('idrombel');
            $filterOptions['TingkatMadin']      = LembagaKelas::madin()->distinct()->orderBy('idtingkat')->pluck('idtingkat');
            $filterOptions['KelasMadin']        = LembagaKelas::madin()->distinct()->orderBy('idrombel')->pluck('idrombel');
            $filterOptions['status_penanganan'] = $this->getEnumValues('penanganan', 'status');
        }

        // Logika Penguncian Lembaga
        $lock     = ['unit_formal' => false, 'AsramaPondok' => false, 'TingkatMadin' => false];
        $selected = ['unit_formal' => null,  'AsramaPondok' => null,  'TingkatMadin' => null];

        if (!$request->ajax()) {
            foreach (['unit_formal', 'AsramaPondok', 'TingkatMadin'] as $f) {
                if (isset($filterOptions[$f]) && in_array($lembagaUser, $filterOptions[$f]->toArray())) {
                    $lock[$f]     = true;
                    $selected[$f] = $lembagaUser;
                }
            }
        }

        // Build Query Utama
        $query      = (clone $scope);
        $allFilters = array_merge($selected, $request->only(array_merge($siswaCols, ['status_penanganan', 'pembayaran_status'])));

        foreach ($siswaCols as $field) {
            $val = $request->get($field, $selected[$field] ?? null);
            if ($val) {
                $query->where($field, $val);
            }
        }

        $now = Carbon::now();

        if ($request->status_penanganan) {
            if ($request->status_penanganan === 'belum_ditangani') {
                $query->whereDoesntHave('penanganan', function ($q) use ($now) {
                    $q->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                });
            } else {
                $query->whereHas('penanganan', function ($q) use ($request) {
                    $q->where('status', $request->status_penanganan);
                });
            }
        }

        if ($request->pembayaran_status) {
            $query->where('sl.is_lunas', $request->pembayaran_status === 'lunas' ? 1 : 0);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $siswa = $query->paginate(40)->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('petugas.siswa.partials.list-siswa', compact('siswa'))->render(),
                'pagination' => $siswa->links()->toHtml(),
            ]);
        }

        return view('petugas.siswa.index', compact('siswa', 'filterOptions', 'lock', 'selected'));
    }

    public function show($id, PembayaranService $pembayaranService)
    {
        $siswa      = Siswa::findOrFail($id);
        $summary    = $pembayaranService->getSummaryPerPeriode((string) $siswa->idperson);
        $belumLunas = $pembayaranService->getDetailBelumLunas((string) $siswa->idperson);

        return view('petugas.siswa.show', compact('siswa', 'summary', 'belumLunas'));
    }

    private function getEnumValues($table, $column)
    {
        $results = \DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);

        if (empty($results)) return [];

        $type = $results[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $values = [];
        if (isset($matches[1])) {
            foreach (explode(',', $matches[1]) as $value) {
                $values[] = trim($value, "'");
            }
        }
        return $values;
    }

    // Sync pembayaran tidak lagi diperlukan — data diambil langsung dari view v_siswa
    // Sync pembayaran sudah tidak diperlukan — data live dari v_siswa
    public function syncAllSummary()
    {
        return response()->json([
            'success' => false,
            'message' => 'Sinkronisasi tidak diperlukan, data pembayaran sudah live dari database.',
        ]);
    }

    public function checkOtherActiveSync()
    {
        return response()->json(['success' => true, 'has_other' => false]);
    }

    public function getActiveBatch()
    {
        return response()->json(['success' => false, 'batch_id' => null]);
    }

    public function cancelSyncSummary()
    {
        return response()->json(['success' => true, 'message' => 'Tidak ada sinkronisasi aktif.']);
    }

    public function getSyncSummaryProgress()
    {
        return response()->json(['success' => false, 'message' => 'Sinkronisasi tidak aktif.'], 404);
    }
}
