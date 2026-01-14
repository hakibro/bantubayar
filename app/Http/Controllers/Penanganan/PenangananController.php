<?php

namespace App\Http\Controllers\Penanganan;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\PenangananHistory;
use App\Models\PenangananKesanggupan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class PenangananController extends Controller
{
    public function index()
    {
        $lembagaUser = auth()->user()->lembaga;
        $query = Penanganan::with(['siswa', 'petugas'])
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('penanganan')
                    ->whereNull('deleted_at')
                    ->groupBy('id_siswa');
            });

        if (Auth::user()->hasRole('petugas')) {
            $data = $query->whereHas('siswa.petugas', function ($q) {
                $q->where('users.id', Auth::id());
            })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

        } else {
            $data = $query->whereHas('siswa', function ($q) use ($lembagaUser) {
                $q->where(function ($sub) use ($lembagaUser) {
                    $sub->where('UnitFormal', $lembagaUser)
                        ->orWhere('AsramaPondok', $lembagaUser)
                        ->orWhere('TingkatDiniyah', $lembagaUser);
                });
            })
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        return view('penanganan.index', compact('data'));
    }

    public function show($id_siswa)
    {
        $siswa = Siswa::findOrFail($id_siswa);

        $penanganan = Penanganan::where('id_siswa', $id_siswa)
            ->with('petugas')
            ->orderBy('created_at', 'desc')
            ->get();


        // Ambil penanganan terakhir
        $penangananTerakhir = $penanganan->first();
        $riwayatAksi = $penangananTerakhir
            ? $penangananTerakhir->histories()->latest()->get()
            : collect();
        return view(
            'penanganan.show',
            compact('siswa', 'penanganan', 'riwayatAksi', 'penangananTerakhir')
        );
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'jenis_penanganan' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        \DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['id_siswa']);
            $penanganan = Penanganan::getOrCreateForSiswa($siswa);
            $penanganan->addHistory(
                $data['jenis_penanganan'],
                $data['catatan'] ?? null
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Aksi Penanganan berhasil disimpan',
        ]);
    }

    public function saveHasil(Request $request)
    {
        $data = $request->validate([
            'id_penanganan' => 'required|exists:penanganan,id',
            'hasil' => 'required|in:lunas,isi_saldo,tidak_ada_respon,hp_tidak_aktif',
            'catatan' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
        ]);

        $penanganan = Penanganan::findOrFail($data['id_penanganan']);

        $penanganan->update([
            'hasil' => $data['hasil'],
            'rating' => $data['rating'],
            'status' => 'selesai',
            'catatan' => $data['catatan'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Hasil penanganan berhasil disimpan.",
        ]);
    }


}
