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
    public function index()
    {
        // Hitung jumlah data siswa
        $count = Siswa::count();

        if ($count === 0) {
            // Tidak ada data siswa → tampilkan tombol sinkronisasi
            return view('admin.assign.get-all-siswa');
        }

        return view('admin.assign.index', [
            'petugas' => User::all(),
            'siswa' => Siswa::with([
                'petugas' => function ($q) {
                    $q->limit(1);
                }
            ])->get(),
        ]);
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
            'siswa_id' => 'required',
            'petugas_id' => 'required|exists:users,id',
        ]);

        // Hapus assign lama
        \DB::table('petugas_siswa')
            ->where('siswa_id', $request->siswa_id)
            ->delete();

        // Insert assign baru
        \DB::table('petugas_siswa')->insert([
            'petugas_id' => $request->petugas_id,
            'siswa_id' => $request->siswa_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Siswa berhasil diassign ke petugas.');
    }

}
