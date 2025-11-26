<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;

class AssignController extends Controller
{
    /** 
     * Halaman utama assign siswa ke petugas
     */
    public function index(Request $request)
    {

        // ambil semua petugas untuk dropdown filter & assign
        $petugas = User::all();

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
            return view('admin.assign.partials.table', compact('siswa', 'petugas'))->render();
        }

        // normal page load -> perlu juga daftar lembaga untuk dropdown (unique)
        $daftarLembaga = Siswa::select('UnitFormal')->distinct()->pluck('UnitFormal')->filter()->sort()->values();
        $daftarAsrama = Siswa::select('AsramaPondok')->distinct()->pluck('AsramaPondok')->filter()->sort()->values();


        return view('admin.assign.index', compact('siswa', 'petugas', 'daftarLembaga', 'daftarAsrama'));
    }

    // endpoint untuk mendapatkan kelas berdasarkan lembaga (dipanggil saat lembaga berubah)
    public function kelas(Request $request)
    {
        $lembaga = $request->lembaga;
        $kelas = Siswa::when($lembaga, fn($q) => $q->where('UnitFormal', $lembaga))
            ->select('KelasFormal')
            ->distinct()
            ->whereNotNull('KelasFormal')
            ->orderBy('KelasFormal')
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
            ->orderBy('KamarPondok')
            ->pluck('KamarPondok');

        return response()->json($kamar);
    }


    /**
     * Assign siswa ke petugas
     */
    public function assign(Request $request)
    {
        $request->validate([
            'petugas_id' => 'required|exists:users,id',
            'siswa_id' => 'required|exists:siswa,id',
        ]);

        $petugas = User::find($request->petugas_id);

        // assign tanpa menghapus relasi lama
        $petugas->siswa()->syncWithoutDetaching([$request->siswa_id]);

        return back()->with('success', 'Siswa berhasil ditautkan.');
    }


    /**
     * Hapus assign siswa dari petugas
     */
    public function unassign(Request $request)
    {
        $request->validate([
            'petugas_id' => 'required|exists:users,id',
            'siswa_id' => 'required|exists:siswa,id',
        ]);

        $petugas = User::find($request->petugas_id);

        $petugas->siswa()->detach($request->siswa_id);

        return back()->with('success', 'Siswa berhasil dihapus dari petugas.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'petugas_id' => 'required|exists:users,id',
        ]);

        foreach ($request->siswa_ids as $id) {
            \DB::table('petugas_siswa')->where('siswa_id', $id)->delete();

            \DB::table('petugas_siswa')->insert([
                'petugas_id' => $request->petugas_id,
                'siswa_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        return back()->with('success', 'Siswa berhasil diassign ke petugas.');
    }


    public function bulk(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'petugas_id' => 'required|exists:users,id'
        ]);

        foreach ($request->siswa_ids as $id) {

            // pastikan siswa ada
            if (!Siswa::find($id))
                continue;

            // hapus petugas sebelumnya
            \DB::table('petugas_siswa')->where('siswa_id', $id)->delete();

            // assign baru
            \DB::table('petugas_siswa')->insert([
                'petugas_id' => $request->petugas_id,
                'siswa_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Berhasil assign petugas ke siswa terpilih.']);
    }
    public function bulkUnassign(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
        ]);

        foreach ($request->siswa_ids as $id) {
            \DB::table('petugas_siswa')->where('siswa_id', $id)->delete();
        }

        return response()->json([
            'message' => 'Berhasil menghapus petugas dari siswa terpilih.'
        ]);
    }



}
