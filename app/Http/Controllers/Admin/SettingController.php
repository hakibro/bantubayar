<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RefreshStatusPembayaranJob;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Halaman pengaturan (berisi section sinkronisasi pembayaran).
     */
    public function index()
    {
        $intervalHours = (int) Setting::get('sync_interval_hours', 6);

        // Waktu terakhir & jumlah ringkasan cache dari tabel siswa_status_pembayaran.
        $lastRow = DB::table('siswa_status_pembayaran')
            ->selectRaw('COUNT(*) AS total, MAX(refreshed_at) AS last_refresh')
            ->first();

        $status = Cache::get(RefreshStatusPembayaranJob::CACHE_KEY);

        return view('admin.pengaturan.index', compact(
            'intervalHours',
            'lastRow',
            'status'
        ));
    }

    /**
     * Simpan interval sinkronisasi otomatis.
     */
    public function updateInterval(Request $request)
    {
        $data = $request->validate([
            'sync_interval_hours' => 'required|in:6,12',
        ]);

        Setting::set('sync_interval_hours', $data['sync_interval_hours']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal sinkronisasi otomatis diperbarui.',
        ]);
    }

    /**
     * Picu refresh cache secara manual (async via queue).
     */
    public function refresh()
    {
        $status = Cache::get(RefreshStatusPembayaranJob::CACHE_KEY);

        if (isset($status['running']) && $status['running']) {
            return response()->json([
                'success' => false,
                'message' => 'Sinkronisasi sedang berjalan. Mohon tunggu hingga selesai.',
            ], 409);
        }

        RefreshStatusPembayaranJob::dispatch();

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi dimulai. Proses berjalan di latar belakang.',
        ]);
    }

    /**
     * Status terkini untuk polling UI.
     */
    public function status()
    {
        $status = Cache::get(RefreshStatusPembayaranJob::CACHE_KEY);

        $lastRow = DB::table('siswa_status_pembayaran')
            ->selectRaw('COUNT(*) AS total, MAX(refreshed_at) AS last_refresh')
            ->first();

        return response()->json([
            'running' => isset($status['running']) && $status['running'],
            'last_finished_at' => $status['last_finished_at'] ?? null,
            'last_total' => $status['last_total'] ?? null,
            'last_error' => $status['last_error'] ?? null,
            'cache_total' => (int) ($lastRow->total ?? 0),
            'cache_last_refresh' => $lastRow->last_refresh,
        ]);
    }
}
