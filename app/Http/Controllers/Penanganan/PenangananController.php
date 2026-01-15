<?php

namespace App\Http\Controllers\Penanganan;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\PenangananHistory;
use App\Models\PenangananKesanggupan;
use App\Models\Siswa;
use App\Services\SiswaService;
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
            'hasil' => 'required|in:lunas,isi_saldo,cicilan,tidak_ada_respon,hp_tidak_aktif',
            'catatan' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
        ]);

        $penanganan = Penanganan::findOrFail($data['id_penanganan']);
        $siswa = Siswa::findOrFail($penanganan->id_siswa);

        // TODO: Jika hasil penanganan lunas, pastikan tidak ada tunggakan
        if ($data['hasil'] === 'lunas') {
            if ($siswa->getTotalTunggakan() < 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Siswa masih memiliki tunggakan sebesar Rp " . number_format($siswa->getTotalTunggakan(), 0, ',', '.'),
                ], 400);
            }
        }
        // TODO: Jika hasil = cicilan, pastikan total tunggakan saat ini < total tunggakan saat penanganan dibuat, ambil kategori pembayaran yang sudah lunas
        if ($data['hasil'] === 'cicilan') {
            if ($siswa->getTotalTunggakan() >= $penanganan->getTotalTunggakan()) {
                return response()->json([
                    'success' => false,
                    'message' => "Total tunggakan saat ini Rp " . number_format($siswa->getTotalTunggakan(), 0, ',', '.') . " harus lebih kecil dari total tunggakan saat penanganan dibuat Rp " . number_format($penanganan->getTotalTunggakan(), 0, ',', '.'),
                ], 400);
            }
        }
        // TODO: Jika hasil = isi_saldo, pastikan saldo saat ini > saldo saat penanganan dibuat
        if ($data['hasil'] === 'isi_saldo') {
            if ($siswa->saldo->saldo <= $penanganan->saldo) {
                return response()->json([
                    'success' => false,
                    'message' => "Saldo saat ini Rp " . number_format($siswa->saldo?->saldo, 0, ',', '.') . " harus lebih besar dari saldo saat penanganan dibuat Rp " . number_format($penanganan->saldo, 0, ',', '.'),
                ], 400);
            }
        }


        // TODO: Jika hasil = tidak_ada_respon atau hp_tidak aktif, pastikan tindak lanjut minimal 3 kali,

        if (in_array($data['hasil'], ['tidak_ada_respon', 'hp_tidak_aktif'])) {

            // hitung jenis penanganan
            $jumlahChat = $penanganan->histories()
                ->where('jenis_penanganan', 'chat')
                ->count();

            $jumlahTelepon = $penanganan->histories()
                ->where('jenis_penanganan', 'phone')
                ->count();


            // validasi minimal aksi
            if ($jumlahChat < 1 || $jumlahTelepon < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal 1x chat dan 2x telepon sebelum memilih hasil ini.',
                ], 422);
            }
            // Jika hp tidak aktif → arahkan update nomor
            if ($data['hasil'] === 'hp_tidak_aktif') {
                return response()->json([
                    'success' => false,
                    'action_required' => 'update_nomor_hp',
                    'message' => 'Nomor HP siswa tidak aktif. Silakan perbarui nomor telepon siswa terlebih dahulu.',
                ], 409);
            }
        }

        $penanganan->update([
            'hasil' => $data['hasil'],
            'rating' => $data['rating'],
            'status' => 'selesai',
            'catatan' => $data['catatan'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Hasil penanganan berhasil disimpan.", // Fungsikan pesan sukses dan error
        ]);
    }

    public function updatePhone(Request $request, SiswaService $siswaService)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'wali' => 'required|string',
            'phone' => 'required|string',
        ]);

        $siswa = Siswa::findOrFail($data['id_siswa']);
        $siswa->update([
            'phone' => $data['phone'] . ' - ' . $data['wali'],
        ]);

        \DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['id_siswa']);
            $penanganan = Penanganan::getOrCreateForSiswa($siswa);
            $penanganan->addHistory(
                'update phone',
                trim('Update No. HP ke ' . $data['phone'] . ' - ' . ($data['wali'] ?? ''))
            );

        });

        return response()->json([
            'success' => true,
            'message' => 'Aksi Penanganan berhasil disimpan',
        ]);

        // Update via SISDA API
        // try {
        //     $result = $siswaService->updateTelepon(
        //         $siswa->idperson,
        //         $data['wali'],
        //         $data['phone']
        //     );

        //     return response()->json([
        //         'success' => true,
        //         'data' => $result,
        //     ]);
        // } catch (\Throwable $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }
    }
}
