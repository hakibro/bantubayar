<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LembagaKelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;

class AssignController extends Controller
{
    public function index(Request $request)
    {
        $petugas = User::role('petugas')->get();

        $query = Siswa::with([
            'petugas' => function ($q) {
                $q->limit(1);
            }
        ]);

        if ($request->filled('search')) {
            $qsearch = $request->search;
            $query->where(function ($q) use ($qsearch) {
                $q->where('nama', 'like', "%{$qsearch}%")
                    ->orWhere('idperson', 'like', "%{$qsearch}%");
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

        if ($request->filled('petugas_id')) {
            $query->whereHas('petugas', function ($q) use ($request) {
                $q->where('users.id', $request->petugas_id);
            });
        }

        $siswa = $query->orderBy('nama')->paginate(40)->withQueryString();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.assign.partials.table', compact('siswa', 'petugas'))->render();
        }

        $daftarLembaga = LembagaKelas::formal()->distinct()->orderBy('title')->pluck('title');
        $daftarAsrama  = LembagaKelas::asrama()->distinct()->orderBy('idtingkat')->pluck('idtingkat');

        return view('admin.assign.index', compact('siswa', 'petugas', 'daftarLembaga', 'daftarAsrama'));
    }

    public function kelas(Request $request)
    {
        $kelas = LembagaKelas::formal()
            ->when($request->lembaga, fn($q) => $q->where('title', $request->lembaga))
            ->distinct()
            ->orderBy('keterangan')
            ->pluck('keterangan');

        return response()->json($kelas);
    }

    public function kamar(Request $request)
    {
        $kamar = LembagaKelas::asrama()
            ->when($request->asrama, fn($q) => $q->where('idtingkat', $request->asrama))
            ->distinct()
            ->orderBy('idrombel')
            ->pluck('idrombel');

        return response()->json($kamar);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'petugas_id' => 'required|exists:users,id',
            'siswa_id'   => 'required|exists:v_siswa,idperson',
        ]);

        $petugas = User::find($request->petugas_id);
        $petugas->siswa()->syncWithoutDetaching([$request->siswa_id]);

        return back()->with('success', 'Siswa berhasil ditautkan.');
    }

    public function unassign(Request $request)
    {
        $request->validate([
            'petugas_id' => 'required|exists:users,id',
            'siswa_id'   => 'required|exists:v_siswa,idperson',
        ]);

        $petugas = User::find($request->petugas_id);
        $petugas->siswa()->detach($request->siswa_id);

        return back()->with('success', 'Siswa berhasil dihapus dari petugas.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_ids'  => 'required|array',
            'petugas_id' => 'required|exists:users,id',
        ]);

        foreach ($request->siswa_ids as $id) {
            \DB::table('petugas_siswa')->where('siswa_id', $id)->delete();
            \DB::table('petugas_siswa')->insert([
                'petugas_id' => $request->petugas_id,
                'siswa_id'   => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Siswa berhasil diassign ke petugas.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'siswa_ids'  => 'required|array',
            'petugas_id' => 'required|exists:users,id',
        ]);

        foreach ($request->siswa_ids as $id) {
            \DB::table('petugas_siswa')->where('siswa_id', $id)->delete();
            \DB::table('petugas_siswa')->insert([
                'petugas_id' => $request->petugas_id,
                'siswa_id'   => $id,
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

        return response()->json(['message' => 'Berhasil menghapus petugas dari siswa terpilih.']);
    }
}
