<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use Illuminate\Http\Request;

class PenangananController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => ['nullable', 'integer'],
            'petugas_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
            'hasil' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $penanganan = Penanganan::query()
            ->with([
                'siswa:idperson,nama',
                'petugas:id,name',
                'histories' => fn($query) => $query
                    ->select('id', 'penanganan_id', 'jenis_penanganan', 'catatan', 'created_at')
                    ->oldest(),
            ])
            ->when($validated['id_siswa'] ?? null, fn($query, $idSiswa) => $query->where('id_siswa', $idSiswa))
            ->when($validated['petugas_id'] ?? null, fn($query, $petugasId) => $query->where('id_petugas', $petugasId))
            ->when($validated['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($validated['hasil'] ?? null, fn($query, $hasil) => $query->where('hasil', $hasil))
            ->when($validated['start_date'] ?? null, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['end_date'] ?? null, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->get()
            ->map(function (Penanganan $item) {
                return [
                    'id' => $item->id,
                    'id_siswa' => $item->id_siswa,
                    'nama_siswa' => $item->siswa?->nama,
                    'nama_petugas' => $item->petugas?->name,
                    'status' => $item->status,
                    'hasil' => $item->hasil,
                    'catatan' => $item->catatan,
                    'history_aksi' => $item->histories->map(fn($history) => [
                        'jenis_penanganan' => $history->jenis_penanganan,
                        'catatan' => $history->catatan,
                        'created_at' => $history->created_at?->toDateTimeString(),
                    ])->values(),
                    'created_at' => $item->created_at?->toDateTimeString(),
                    'updated_at' => $item->updated_at?->toDateTimeString(),
                ];
            });

        return response()->json($penanganan);
    }
}