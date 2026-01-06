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
        $lembagaUser = auth()->user()->lembaga;

        $data = Penanganan::with(['siswa', 'petugas'])
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('penanganan')
                    ->whereNull('deleted_at')
                    ->groupBy('id_siswa');
            })
            ->whereHas('siswa', function ($q) use ($lembagaUser) {
                $q->where(function ($sub) use ($lembagaUser) {
                    $sub->where('UnitFormal', $lembagaUser)
                        ->orWhere('AsramaPondok', $lembagaUser)
                        ->orWhere('TingkatDiniyah', $lembagaUser);
                });
            })
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

        // Ambil penanganan terakhir
        $penangananTerakhir = $penanganan->first();

        // Boleh buat penanganan baru jika:
        // - belum pernah ada penanganan
        // - atau status terakhir = selesai
        $bolehBuatPenanganan =
            is_null($penangananTerakhir) ||
            $penangananTerakhir->status === 'selesai';

        return view(
            'penanganan.index-siswa',
            compact('siswa', 'penanganan', 'bolehBuatPenanganan', 'penangananTerakhir')
        );
    }


    public function create($siswa_id)
    {
        $penangananTerakhir = Penanganan::where('id_siswa', $siswa_id)
            ->latest()
            ->first();

        if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai') {
            return redirect()
                ->route('penanganan.index-siswa', $$siswa_id)
                ->with('error', 'Masih ada penanganan yang belum selesai.');
        }

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
        $status = 'menunggu_respon'; // default saat penanganan dibuat

        if ($request->filled('hasil')) {
            switch ($request->hasil) {
                case 'lunas':
                case 'tidak_ada_respon':
                    $status = 'selesai';
                    break;

                case 'rekom_isi_saldo':
                    $status = 'menunggu_tindak_lanjut';
                    break;
                case 'rekom_tidak_isi_saldo':
                    $status = 'menunggu_tindak_lanjut';
                    break;
            }
        }

        Penanganan::create([
            'id_siswa' => $siswa->id,
            'id_petugas' => Auth::id(),
            'jenis_pembayaran' => $jenisPembayaran, // AUTO
            'jenis_penanganan' => $request->jenis_penanganan,
            'catatan' => $request->catatan ?? Null,
            'hasil' => $request->hasil ?? Null,
            'tanggal_rekom' => $request->tanggal_rekom ?? Null,
            'status' => $status,
        ]);

        return redirect()->route('penanganan.index')->with('success', 'Penanganan siswa berhasil disimpan.');
    }

    public function edit($id)
    {
        $penanganan = Penanganan::with(['siswa', 'petugas'])->findOrFail($id);
        // hanya petugas yang membuat penanganan yang boleh mengupdate
        if ($penanganan->id_petugas !== Auth::id()) {
            return redirect()
                ->back()
                ->with('error', 'Hanya petugas yang membuat penanganan yang boleh mengupdate.');
        }

        // 🔒 Opsional: cegah update jika sudah selesai
        if ($penanganan->status === 'selesai') {
            return redirect()
                ->back()
                ->with('error', 'Penanganan yang sudah selesai tidak dapat diubah.');
        }

        // Hanya penanganan terakhir siswa yang boleh di update
        $terakhir = Penanganan::where('id_siswa', $penanganan->id_siswa)
            ->latest()
            ->first();

        if ($terakhir->id !== $penanganan->id) {
            return back()->with('error', 'Hanya penanganan terakhir yang dapat diedit.');
        }
        // 🔒 Opsional: cegah edit jika sudah selesai
        if ($penanganan->status === 'selesai') {
            return redirect()
                ->back()
                ->with('error', 'Penanganan yang sudah selesai tidak dapat diedit.');
        }

        $siswa = Siswa::with('pembayaran')->findOrFail($penanganan->id_siswa);

        // Ambil kategori belum lunas (jika masih relevan)
        $kategoriBelumLunas = $siswa->getKategoriBelumLunas();

        return view('penanganan.edit', compact(
            'penanganan',
            'siswa',
            'kategoriBelumLunas'
        ));
    }
    public function update(Request $request, $id)
    {
        $penanganan = Penanganan::findOrFail($id);


        $request->validate([
            'jenis_penanganan' => 'required',
            'hasil' => 'nullable',
            'tanggal_rekom' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        // =============================
        // AUTO SET STATUS (SAMA DENGAN STORE)
        // =============================
        $status = $penanganan->status; // default

        if ($request->filled('hasil')) {
            switch ($request->hasil) {
                case 'lunas':
                case 'tidak_ada_respon':
                    $status = 'selesai';
                    break;

                case 'rekom_isi_saldo':
                case 'rekom_tidak_isi_saldo':
                    $status = 'menunggu_tindak_lanjut';
                    break;

                default:
                    $status = 'menunggu_respon';
            }
        }

        $penanganan->update([
            'jenis_penanganan' => $request->jenis_penanganan,
            'catatan' => $request->catatan,
            'hasil' => $request->hasil,
            'tanggal_rekom' => $request->tanggal_rekom,
            'status' => $status,
        ]);

        return redirect()
            ->route('penanganan.siswa', $penanganan->id_siswa)
            ->with('success', 'Penanganan berhasil diperbarui.');
    }




}
