<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Services\PembayaranService;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $petugas = User::role('petugas')->get();

        $query = Siswa::query()
            ->leftJoin('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_siswa.idperson')
            ->select('v_siswa.*', 'sl.is_lunas')
            ->with([
                'petugas' => function ($q) {
                    $q->limit(1);
                }
            ]);

        if ($request->filled('search')) {
            $qsearch = $request->search;
            $query->where(function ($q) use ($qsearch) {
                $q->where('v_siswa.nama', 'like', "%{$qsearch}%")
                    ->orWhere('v_siswa.idperson', 'like', "%{$qsearch}%");
            });
        }

        if ($request->filled('lembaga')) {
            if ($request->lembaga === '__NULL__') {
                $query->whereNull('unit_formal');
            } else {
                $query->where('unit_formal', $request->lembaga);
            }
        }

        if ($request->filled('kelas')) {
            $query->where('kelas_formal', $request->kelas);
        }

        if ($request->filled('asrama')) {
            if ($request->asrama === '__NULL__') {
                $query->whereNull('AsramaPondok');
            } else {
                $query->where('AsramaPondok', $request->asrama);
            }
        }

        if ($request->filled('kamar')) {
            $query->where('KamarPondok', $request->kamar);
        }

        if ($request->filled('diniyah')) {
            if ($request->diniyah === '__NULL__') {
                $query->whereNull('TingkatMadin');
            } else {
                $query->where('TingkatMadin', $request->diniyah);
            }
        }

        if ($request->filled('kelasdiniyah')) {
            $query->where('KelasMadin', $request->kelasdiniyah);
        }

        if ($request->filled('petugas_id')) {
            $query->whereHas('petugas', function ($q) use ($request) {
                $q->where('users.id', $request->petugas_id);
            });
        }

        if ($request->filled('pembayaran_status')) {
            $query->where('sl.is_lunas', $request->pembayaran_status === 'lunas' ? 1 : 0);
        }

        $siswa = $query->orderBy('v_siswa.nama')->paginate(40)->withQueryString();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.siswa.partials.table', compact('siswa', 'petugas'))->render();
        }

        $daftarLembaga = Siswa::select('unit_formal')->distinct()->pluck('unit_formal')->filter()->sort()->values();
        $daftarAsrama  = Siswa::select('AsramaPondok')->distinct()->pluck('AsramaPondok')->filter()->sort()->values();
        $daftarDiniyah = Siswa::select('TingkatMadin')->distinct()->pluck('TingkatMadin')->filter()->sort()->values();

        return view('admin.siswa.index', compact('siswa', 'petugas', 'daftarLembaga', 'daftarAsrama', 'daftarDiniyah'));
    }

    public function kelas(Request $request)
    {
        $lembaga = $request->lembaga;
        $kelas = Siswa::when($lembaga, fn($q) => $q->where('unit_formal', $lembaga))
            ->select('kelas_formal')
            ->distinct()
            ->whereNotNull('kelas_formal')
            ->orderBy('kelas_formal')
            ->pluck('kelas_formal');

        return response()->json($kelas);
    }

    public function kamar(Request $request)
    {
        $asrama = $request->asrama;
        $kamar = Siswa::when($asrama, fn($q) => $q->where('AsramaPondok', $asrama))
            ->select('KamarPondok')
            ->distinct()
            ->whereNotNull('KamarPondok')
            ->orderBy('KamarPondok')
            ->pluck('KamarPondok');

        return response()->json($kamar);
    }

    public function kelasDiniyah(Request $request)
    {
        $diniyah = $request->diniyah;
        $kelasDiniyah = Siswa::when($diniyah, fn($q) => $q->where('TingkatMadin', $diniyah))
            ->select('KelasMadin')
            ->distinct()
            ->whereNotNull('KelasMadin')
            ->orderBy('KelasMadin')
            ->pluck('KelasMadin');

        return response()->json($kelasDiniyah);
    }

    public function show($id, PembayaranService $pembayaranService)
    {
        $siswa      = Siswa::findOrFail($id);
        $summary    = $pembayaranService->getSummaryPerPeriode((string) $siswa->idperson);
        $belumLunas = $pembayaranService->getDetailBelumLunas((string) $siswa->idperson);

        return view('admin.siswa.show', compact('siswa', 'summary', 'belumLunas'));
    }
}
