<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {

        $siswaAll = Siswa::all();

        // Cek apakah tidak ada data sama sekali
        if ($siswaAll->isEmpty()) {
            // Kembalikan view khusus ketika tidak ada data siswa
            return view('admin.siswa.get-all-siswa');
        }

        // ambil semua petugas untuk dropdown filter & assign
        $petugas = User::role('petugas')->get();

        // build query siswa dengan eager load petugas (limit 1)
        $query = Siswa::with([
            'petugas' => function ($q) {
                $q->limit(1);
            }
        ]);

        // filter search
        if ($request->filled('search')) {
            $qsearch = $request->search;
            $query->where(function ($q) use ($qsearch) {
                $q->where('nama', 'like', "%{$qsearch}%")
                    ->orWhere('idperson', 'like', "%{$qsearch}%");
            });
        }

        // filter lembaga
        if ($request->filled('lembaga')) {
            if ($request->lembaga === '__NULL__') {
                $query->whereNull('UnitFormal');
            } else {
                $query->where('UnitFormal', $request->lembaga);
            }
        }

        // filter kelas
        if ($request->filled('kelas')) {
            $query->where('KelasFormal', $request->kelas);
        }

        // filter asrama
        if ($request->filled('asrama')) {
            if ($request->asrama === '__NULL__') {
                $query->whereNull('AsramaPondok');
            } else {
                $query->where('AsramaPondok', $request->asrama);
            }
        }
        // filter kamar
        if ($request->filled('kamar')) {
            $query->where('KamarPondok', $request->kamar);
        }



        // filter by petugas (whereHas pivot)
        if ($request->filled('petugas_id')) {
            $query->whereHas('petugas', function ($q) use ($request) {
                $q->where('users.id', $request->petugas_id);
            });
        }

        // ambil hasil (boleh tambahkan paginate jika perlu)
        $siswa = $query->orderBy('nama')->paginate(40)->withQueryString();


        // jika request ajax (fetch XHR), kembalikan partial table saja
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.siswa.partials.table', compact('siswa', 'petugas'))->render();
        }

        // normal page load -> perlu juga daftar lembaga untuk dropdown (unique)
        $daftarLembaga = Siswa::select('UnitFormal')->distinct()->pluck('UnitFormal')->filter()->sort()->values();
        $daftarAsrama = Siswa::select('AsramaPondok')->distinct()->pluck('AsramaPondok')->filter()->sort()->values();

        return view('admin.siswa.index', compact('siswa', 'petugas', 'daftarLembaga', 'daftarAsrama'));

    }

    public function kelas(Request $request)
    {
        $lembaga = $request->lembaga;
        $kelas = Siswa::when($lembaga, fn($q) => $q->where('UnitFormal', $lembaga))
            ->select('KelasFormal')
            ->distinct()
            ->whereNotNull('KelasFormal')
            ->orderBy('KelasFormal', 'asc')
            ->pluck('KelasFormal');

        return response()->json($kelas);
    }
    public function kamar(Request $request)
    {
        $asrama = $request->asrama;
        $kamar = Siswa::when($asrama, fn($q) => $q->where('AsramaPondok', $asrama))
            ->select('KamarPondok')
            ->distinct()
            ->whereNotNull('KamarPondok')
            ->orderBy('KamarPondok', 'asc')
            ->pluck('KamarPondok');

        return response()->json($kamar);
    }

    public function show($id)
    {
        $siswa = Siswa::with([
            'pembayaran' => function ($q) {
                $q->orderBy('periode', 'desc');
            }
        ])->findOrFail($id);

        return view('admin.siswa.show', compact('siswa'));
    }


}
