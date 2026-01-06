<?php

namespace App\Http\Controllers\Penanganan;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


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
                ->route('penanganan.index-siswa', $siswa_id)
                ->with('error', 'Masih ada penanganan yang belum selesai.');
        }

        $siswa = Siswa::with('pembayaran')->findOrFail($siswa_id);

        $kategoriBelumLunas = $siswa->getKategoriBelumLunas();

        if (count($kategoriBelumLunas) == 0) {
            return redirect()->back()->with('error', 'Tidak ada tunggakan.');
        }

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
            'hasil' => 'nullable|in:lunas,isi_saldo,rekomendasi,tidak_ada_respon',
            'tanggal_rekom' => 'nullable|date',
            'catatan' => 'nullable|string',
            'bukti_pembayaran' => 'nullable|image|max:2048',
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

        // Validasi: jika hasil lunas / isi saldo, wajib ada bukti pembayaran
        if (in_array($request->hasil, ['lunas', 'isi_saldo'])) {
            if (!$request->hasFile('bukti_pembayaran') && !$penanganan->bukti_pembayaran) {
                return back()
                    ->withInput()
                    ->with('error', 'Bukti pembayaran wajib diunggah untuk hasil lunas / isi saldo.');
            }
        }


        $buktiPath = $penanganan->bukti_pembayaran;

        if (in_array($request->hasil, ['lunas', 'isi_saldo'])) {

            if (
                $request->hasFile('bukti_pembayaran') &&
                $request->file('bukti_pembayaran')->isValid()
            ) {

                try {
                    $file = $request->file('bukti_pembayaran');

                    // Validasi file size
                    if (!$file || $file->getSize() === 0) {
                        return back()
                            ->withInput()
                            ->with('error', 'File bukti pembayaran kosong atau tidak valid. Silakan coba upload ulang.');
                    }

                    // Validasi MIME type
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        return back()
                            ->withInput()
                            ->with('error', 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
                    }

                    // hapus file lama
                    if (
                        !empty($penanganan->bukti_pembayaran) &&
                        Storage::disk('public')->exists($penanganan->bukti_pembayaran)
                    ) {
                        Storage::disk('public')->delete($penanganan->bukti_pembayaran);
                    }

                    // Dapatkan extension dari MIME type jika extension kosong
                    $ext = $file->extension();
                    if (empty($ext)) {
                        $mimeToExt = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                        ];
                        $ext = $mimeToExt[$file->getMimeType()] ?? 'jpg';
                    }

                    // upload baru dengan nama yang lebih unique
                    $filename = 'bukti_' . $penanganan->id . '_' . time() . '.' . $ext;
                    $buktiPath = $file->storeAs('bukti-pembayaran', $filename, 'public');

                    if (empty($buktiPath)) {
                        throw new \Exception('Gagal menyimpan file ke storage.');
                    }
                } catch (\Exception $e) {
                    return back()
                        ->withInput()
                        ->with('error', 'Gagal mengupload file: ' . $e->getMessage());
                }
            }

        } else {

            // hasil bukan lunas / isi saldo → hapus bukti
            if (
                !empty($penanganan->bukti_pembayaran) &&
                Storage::disk('public')->exists($penanganan->bukti_pembayaran)
            ) {
                Storage::disk('public')->delete($penanganan->bukti_pembayaran);
            }

            $buktiPath = null;
        }



        $penanganan->update([
            'jenis_penanganan' => $request->jenis_penanganan,
            'catatan' => $request->catatan,
            'hasil' => $request->hasil,
            'tanggal_rekom' => $request->hasil === 'rekomendasi'
                ? $request->tanggal_rekom
                : null,
            'status' => $status,
            'bukti_pembayaran' => $buktiPath,
        ]);



        return redirect()
            ->route('penanganan.siswa', $penanganan->id_siswa)
            ->with('success', 'Penanganan berhasil diperbarui.');
    }




}
