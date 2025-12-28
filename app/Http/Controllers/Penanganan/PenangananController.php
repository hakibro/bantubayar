<?php

namespace App\Http\Controllers\Penanganan;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenangananController extends Controller
{
    public function index()
    {
        $data = Penanganan::with(['siswa', 'petugas'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('penanganan.index', compact('data'));
    }
    public function indexSiswa($id_siswa)
    {
        $siswa = Siswa::findOrFail($id_siswa);

        $penanganan = Penanganan::where('id_siswa', $id_siswa)
            ->with('petugas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('penanganan.index-siswa', compact('siswa', 'penanganan'));
    }


    public function create($siswa_id)
    {
        $siswa = Siswa::with('pembayaran')->findOrFail($siswa_id);

        $kategoriBelumLunas = $siswa->getKategoriBelumLunas();

        if (count($kategoriBelumLunas) == 0) {
            return redirect()->back()->with('error', 'Tidak ada tunggakan.');
        }

        // TODO tambahkan pengecekan apakah sudah ada penanganan aktif?
        // TODO batasi akses hanya untuk petugas yang ditugaskan ke siswa ini
        // TODO tambahkan QR code wa untuk menghubungi wali siswa

        return view('penanganan.create', compact('siswa', 'kategoriBelumLunas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_siswa' => 'required',
            'jenis_penanganan' => 'required'
        ]);

        $siswa = Siswa::with('pembayaran')->findOrFail($request->id_siswa);

        // Ambil semua kategori belum lunas otomatis
        $jenisPembayaran = $siswa->getKategoriBelumLunas();


        // TODO tambahkan auto set status berdasarkan jenis penanganan dan hasil


        Penanganan::create([
            'id_siswa' => $siswa->id,
            'id_petugas' => Auth::id(),
            'jenis_pembayaran' => $jenisPembayaran, // AUTO
            'jenis_penanganan' => $request->jenis_penanganan,
            'catatan' => $request->catatan ?? Null,
            'hasil' => $request->hasil ?? Null,
            'tanggal_rekom' => $request->tanggal_rekom ?? Null,
            'status' => $request->status ?? Null,
        ]);

        return redirect()->route('penanganan.index')->with('success', 'Penanganan siswa berhasil disimpan.');
    }

}
