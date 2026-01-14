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

        // dd($penangananTerakhir);

        // Boleh buat penanganan baru jika:
        // - belum pernah ada penanganan
        // - atau status terakhir = selesai
        $bolehBuatPenanganan =
            is_null($penangananTerakhir) ||
            $penangananTerakhir->status === 'selesai';

        $riwayatAksi = PenangananHistory::whereIn('penanganan_id', function ($query) use ($id_siswa) {
            $query->select('id')
                ->from('penanganan')
                ->where('id_siswa', $id_siswa)
                ->whereNull('deleted_at');
        })
            ->with('penanganan.petugas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view(
            'penanganan.show',
            compact('siswa', 'penanganan', 'riwayatAksi', 'bolehBuatPenanganan', 'penangananTerakhir')
        );
    }


    // public function create($siswa_id)
    // {
    //     $penangananTerakhir = Penanganan::where('id_siswa', $siswa_id)
    //         ->latest()
    //         ->first();

    //     if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai') {
    //         return redirect()
    //             ->route('penanganan.show', $siswa_id)
    //             ->with('error', 'Masih ada penanganan yang belum selesai.');
    //     }

    //     $siswa = Siswa::with('pembayaran')->findOrFail($siswa_id);

    //     $kategoriBelumLunas = $siswa->getKategoriBelumLunas();

    //     if (count($kategoriBelumLunas) == 0) {
    //         return redirect()->back()->with('error', 'Tidak ada tunggakan.');
    //     }

    //     return view('penanganan.create', compact('siswa', 'kategoriBelumLunas'));
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'id_siswa' => 'required',
    //         'jenis_penanganan' => 'required'
    //     ]);

    //     $siswa = Siswa::with('pembayaran')->findOrFail($request->id_siswa);

    //     // Ambil semua kategori belum lunas otomatis
    //     $jenisPembayaran = $siswa->getKategoriBelumLunas();
    //     $saldo = $siswa->saldo->saldo ?? 0;


    //     // TODO tambahkan auto set status berdasarkan jenis penanganan dan hasil
    //     $status = 'menunggu_respon';

    //     switch ($request->hasil) {
    //         case 'lunas':
    //             $status = 'selesai';
    //             break;

    //         case 'isi_saldo':
    //             $status = 'selesai';
    //             break;

    //         case 'rekomendasi':
    //             $status = 'menunggu_tindak_lanjut';
    //             break;

    //         case 'tidak_ada_respon':
    //             $status = 'selesai';
    //             break;
    //     }


    //     Penanganan::create([
    //         'id_siswa' => $siswa->id,
    //         'id_petugas' => Auth::id(),
    //         'jenis_pembayaran' => $jenisPembayaran, // AUTO
    //         'saldo' => $saldo, // AUTO
    //         'jenis_penanganan' => $request->jenis_penanganan,
    //         'catatan' => $request->catatan ?? Null,
    //         'hasil' => $request->hasil ?? Null,
    //         'tanggal_rekom' => $request->tanggal_rekom ?? Null,
    //         'status' => $status,
    //     ]);

    //     return redirect()->route('penanganan.show', $siswa->id)->with('success', 'Penanganan siswa berhasil disimpan.');
    // }

    public function store(Request $request)
    {
        $request->validate([
            'id_siswa' => 'required',
            'jenis_penanganan' => 'required',
            'catatan' => 'nullable|string',
        ]);

        $siswa = Siswa::with('pembayaran')->findOrFail($request->id_siswa);

        // Ambil semua kategori belum lunas otomatis
        $jenisPembayaran = $siswa->getKategoriBelumLunas();
        $saldo = $siswa->saldo->saldo ?? 0;

        // simpan penanganan baru
        Penanganan::create([
            'id_siswa' => $siswa->id,
            'id_petugas' => Auth::id(),
            'jenis_pembayaran' => $jenisPembayaran, // AUTO
            'saldo' => $saldo, // AUTO
            'status' => 'menunggu_respon',
        ]);

        // ambil id penanganan terakhir yang baru dibuat
        $penangananTerbaru = Penanganan::where('id_siswa', $siswa->id)
            ->latest()
            ->first();

        // simpan history penanganan
        PenangananHistory::create([
            'penanganan_id' => $penangananTerbaru->id,
            'jenis_penanganan' => $request->jenis_penanganan,
            'catatan' => $request->catatan ?? Null,
        ]);
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
            'hasil' => 'nullable|in:lunas,isi_saldo,rekomendasi,tidak_ada_respon',
            'tanggal_rekom' => 'required_if:hasil,rekomendasi|date',
            'catatan' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
        ]);


        // =============================
        // AUTO SET STATUS (SAMA DENGAN STORE)
        // =============================
        $status = 'menunggu_respon';

        switch ($request->hasil) {
            case 'lunas':
                $status = 'selesai';
                break;

            case 'isi_saldo':
                $status = 'selesai';
                break;

            case 'rekomendasi':
                $status = 'menunggu_tindak_lanjut';
                break;

            case 'tidak_ada_respon':
                $status = 'selesai';
                break;
        }


        $penanganan->update([
            'jenis_penanganan' => $request->jenis_penanganan,
            'catatan' => $request->catatan,
            'hasil' => $request->hasil,
            'tanggal_rekom' => $request->hasil === 'rekomendasi' ? $request->tanggal_rekom : null,
            'status' => $status,
            'rating' => $request->rating,
        ]);



        return redirect()
            ->route('penanganan.show', $penanganan->id_siswa)
            ->with('success', 'Penanganan berhasil diperbarui.');
    }

}
