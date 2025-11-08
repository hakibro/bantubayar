@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white shadow rounded-lg">
            <h3 class="text-gray-700 text-sm font-semibold">Total Pengguna</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">1,204</p>
        </div>

        <div class="p-6 bg-white shadow rounded-lg">
            <h3 class="text-gray-700 text-sm font-semibold">Transaksi Bulan Ini</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">Rp 12.450.000</p>
        </div>

        <div class="p-6 bg-white shadow rounded-lg">
            <h3 class="text-gray-700 text-sm font-semibold">Laporan Belum Dicek</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">18</p>
        </div>
    </div>

    <div class="mt-8 bg-white p-6 shadow rounded-lg">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Aktivitas Terbaru</h3>
        <ul class="divide-y divide-gray-200">
            <li class="py-3 flex justify-between">
                <span>🧾 Pembayaran #2025-001 disetujui</span>
                <span class="text-sm text-gray-500">2 jam lalu</span>
            </li>
            <li class="py-3 flex justify-between">
                <span>👤 Pengguna baru ditambahkan: <b>Ahmad Bayhaki</b></span>
                <span class="text-sm text-gray-500">5 jam lalu</span>
            </li>
            <li class="py-3 flex justify-between">
                <span>📈 Sistem di-update ke versi terbaru</span>
                <span class="text-sm text-gray-500">1 hari lalu</span>
            </li>
        </ul>
    </div>
@endsection
