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
use Illuminate\Support\Facades\URL;


class PenangananController extends Controller
{
    public function index()
    {
        $data = auth()->user()
            ->penanganan()
            ->with('siswa')
            ->orderBy('status', 'asc')
            ->paginate(20);

        return view('penanganan.index', compact('data'));
    }

    public function show(Request $request, $id_siswa)
    {
        $siswa = Siswa::with('pembayaran')->findOrFail($id_siswa);



        // 2. CEK AKSES PETUGAS (Harus Login)
        if (auth()->check()) {
            $penanganan = Penanganan::where('id_siswa', $id_siswa)
                ->with('petugas')
                ->orderBy('created_at', 'desc')
                ->get();

            $urlUntukWali = URL::temporarySignedRoute(
                'penanganan.show', // Nama route Anda
                now()->addDays(30), // Link berlaku 30 hari
                ['id_siswa' => $siswa->id]
            );

            $penangananTerakhir = $penanganan->first();
            $riwayatAksi = $penangananTerakhir
                ? $penangananTerakhir->histories()->latest()->get()
                : collect();


            return view(
                'penanganan.show', // View detail penanganan untuk petugas
                compact('siswa', 'penanganan', 'riwayatAksi', 'penangananTerakhir', 'urlUntukWali')
            );
        }

        // 1. CEK AKSES WALI SISWA (Tanpa Login via Signed URL)
        if ($request->hasValidSignature() || app()->environment('local')) {
            // Ambil data pembayaran untuk ditampilkan ke wali

            // dd($siswa->getKategoriBelumLunas());

            $pembayaran = $siswa->getKategoriBelumLunas();
            return view('penanganan.wali_pembayaran', compact('siswa', 'pembayaran'));
        }

        // Jika tidak keduanya, tolak akses
        abort(403, 'Akses ditolak. Link tidak valid atau Anda tidak memiliki akses.');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'jenis_penanganan' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $result = \DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['id_siswa']);
            $penanganan = Penanganan::getOrCreateForSiswa($siswa);

            // skip jika bukan petugas yang membuat penanganan
            if ($penanganan->id_petugas !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'Sedang ditangani oleh ' . Auth::user()->name,
                ];
            }

            // jika ada history telepon terakhir kurang dari 1 hari, tolak
            if (
                $data['jenis_penanganan'] === 'phone' &&
                $penanganan->histories()
                    ->where('jenis_penanganan', 'phone')
                    ->where('created_at', '>=', now()->startOfDay())
                    ->exists()
            ) {
                return [
                    'success' => false,
                    'message' => 'Aksi telepon terakhir kurang dari 1 hari yang lalu.',
                ];
            }

            $penanganan->addHistory(
                $data['jenis_penanganan'],
                $data['catatan'] ?? null
            );
            return [
                'success' => true,
                'message' => 'Aksi Penanganan berhasil disimpan',
            ];


        });
        return response()->json($result, $result['success'] ? 200 : 422);
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

        // Jika hasil penanganan lunas, pastikan tidak ada tunggakan
        if ($data['hasil'] === 'lunas') {
            if ($siswa->getTotalTunggakan() < 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Siswa masih memiliki tunggakan sebesar Rp " . number_format($siswa->getTotalTunggakan(), 0, ',', '.'),
                ], 400);
            }
        }
        // Jika hasil = cicilan, pastikan total tunggakan saat ini < total tunggakan saat penanganan dibuat, ambil kategori pembayaran yang sudah lunas
        if ($data['hasil'] === 'cicilan') {
            if ($siswa->getTotalTunggakan() >= $penanganan->getTotalTunggakan()) {
                return response()->json([
                    'success' => false,
                    'message' => "Total tunggakan saat ini Rp " . number_format($siswa->getTotalTunggakan(), 0, ',', '.') . " harus lebih kecil dari total tunggakan saat penanganan dibuat Rp " . number_format($penanganan->getTotalTunggakan(), 0, ',', '.'),
                ], 400);
            }
        }
        // Jika hasil = isi_saldo, pastikan saldo saat ini > saldo saat penanganan dibuat
        if ($data['hasil'] === 'isi_saldo') {
            if ($siswa->saldo->saldo <= $penanganan->saldo) {
                return response()->json([
                    'success' => false,
                    'message' => "Saldo saat ini Rp " . number_format($siswa->saldo?->saldo, 0, ',', '.') . " harus lebih besar dari saldo saat penanganan dibuat Rp " . number_format($penanganan->saldo, 0, ',', '.'),
                ], 400);
            }
        }


        // Jika hasil = tidak_ada_respon atau hp_tidak aktif, pastikan tindak lanjut minimal 3 kali,

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

        // TODO: pastikan mengupdate no.hp sesuai wali terutama jika ada 2 no hp
        $siswa->update([
            'phone' => $data['phone'] . ' - ' . $data['wali'],
        ]);

        \DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['id_siswa']);
            $penanganan = Penanganan::getOrCreateForSiswa($siswa);
            // skip jika bukan petugas yang membuat penanganan
            if ($penanganan->id_petugas !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sedang ditangani oleh' . Auth::getName(),
                ]);
            }
            $penanganan->addHistory(
                'update phone',
                trim('Update No. HP ke ' . $data['phone'] . ' - ' . ($data['wali'] ?? ''))
            );

        });



        // Update via SISDA API
        try {
            $result = $siswaService->updateTelepon(
                $siswa->idperson,
                $data['wali'],
                $data['phone']
            );

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menyimpan No. HP Wali ' . $siswa->nama . ': ' . $data['phone'] . ' - ' . $data['wali'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }


    // Kesanggupan
    public function kirimKesanggupan(Request $request)
    {
        try {
            $data = $request->validate([
                'penanganan_id' => 'required|exists:penanganan,id',
                'tanggal_kesanggupan' => 'required|date',
            ]);



            $kesanggupan = PenangananKesanggupan::create([
                'penanganan_id' => $data['penanganan_id'],
                'tanggal' => $data['tanggal_kesanggupan'],
                'token' => \Str::uuid(),
            ]);

            $kesanggupan->penanganan()->update([
                'status' => 'menunggu_tindak_lanjut',
            ]);

            return response()->json([
                'success' => true,
                'link' => route('wali.kesanggupan.form', $kesanggupan->token)
            ]);

        } catch (\Throwable $e) {
            logger()->error('KESANGGUPAN ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }



    public function formKesanggupan($token)
    {
        $kesanggupan = PenangananKesanggupan::where('token', $token)->firstOrFail();
        return view('penanganan.kesanggupan', compact('kesanggupan'));
    }

    public function submitKesanggupan(Request $request, $token)
    {
        $data = $request->validate([
            // Ubah min:0 menjadi min:1 atau gt:0 agar tidak menerima 0
            'nominal' => 'required|numeric|min:1',
        ]);

        $kesanggupan = PenangananKesanggupan::where('token', $token)->firstOrFail();

        // optional: cegah submit ulang
        // if ($kesanggupan->nominal !== null) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Kesanggupan sudah pernah dikirim'
        //     ], 422);
        // }

        $kesanggupan->update([
            'nominal' => $data['nominal']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kesanggupan berhasil dikirim'
        ]);
    }
}
