@extends('layouts.dashboard')

@section('title', 'Dashboard Bendahara')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 bg-white shadow rounded-lg">
            <h3 class="text-gray-700 text-sm font-semibold">Total Lembaga Diawasi</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">12</p>
        </div>

        <div class="p-6 bg-white shadow rounded-lg">
            <h3 class="text-gray-700 text-sm font-semibold">Laporan Terkirim</h3>
            <p class="text-2xl font-bold mt-2 text-indigo-600">37</p>
        </div>
    </div>

    <div class="mt-8 bg-white p-6 shadow rounded-lg">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Laporan Terbaru</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Tanggal</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Nama Lembaga</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-2 text-sm">6 Nov 2025</td>
                    <td class="px-4 py-2 text-sm">SMP Al-Hikmah</td>
                    <td class="px-4 py-2 text-sm text-green-600 font-semibold">Selesai</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-sm">5 Nov 2025</td>
                    <td class="px-4 py-2 text-sm">MA Darussalam</td>
                    <td class="px-4 py-2 text-sm text-yellow-600 font-semibold">Menunggu</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
