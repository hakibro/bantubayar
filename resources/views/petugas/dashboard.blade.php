@extends('layouts.dashboard')

@section('title', 'Dashboard Bendahara')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- ================= SUMMARY CARDS ================= --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

            <!-- Total Penanganan -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-700">Total Penanganan</p>
                <p class="text-2xl font-bold text-blue-900">
                    {{ $summary['total'] }}
                </p>
            </div>

            <!-- Menunggu Respon -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm text-yellow-700">Menunggu Respon</p>
                <p class="text-2xl font-bold text-yellow-900">
                    {{ $summary['menunggu_respon'] }}
                </p>
            </div>

            <!-- Menunggu Tindak Lanjut -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <p class="text-sm text-indigo-700">Menunggu Tindak Lanjut</p>
                <p class="text-2xl font-bold text-indigo-900">
                    {{ $summary['menunggu_tindak_lanjut'] }}
                </p>
            </div>

            <!-- Selesai -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <p class="text-sm text-green-700">Selesai</p>
                <p class="text-2xl font-bold text-green-900">
                    {{ $summary['selesai'] }}
                </p>
            </div>

            <!-- Tidak Ada Respon -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-sm text-red-700">Tidak Ada Respon</p>
                <p class="text-2xl font-bold text-red-900">
                    {{ $summary['tidak_ada_respon'] }}
                </p>
            </div>

        </div>


        {{-- ================= TUGAS AKTIF ================= --}}
        <div class="bg-white rounded-xl shadow border">
            <div class="px-5 py-4 border-b font-semibold text-gray-700">
                Tugas Aktif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Siswa</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Menunggu</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($tugasAktif as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $item->siswa->nama ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->siswa->idperson ?? '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 capitalize">
                                    {{ str_replace('_', ' ', $item->jenis_penanganan) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex px-2 py-1 rounded-full text-xs font-medium
    @if ($item->status == 'menunggu_respon') bg-yellow-100 text-yellow-800
    @elseif($item->status == 'menunggu_tindak_lanjut') bg-indigo-100 text-indigo-800
    @elseif($item->status == 'selesai') bg-green-100 text-green-800
    @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                    </span>


                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $item->lama_menunggu }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('penanganan.siswa', $item->siswa->id) }}"
                                        class="text-blue-600 hover:underline">
                                        Lanjutkan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    Tidak ada tugas aktif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= PENANGANAN TERLAMBAT ================= --}}
        @if ($penangananTerlambat->count())
            <div class="bg-red-50 border border-red-200 rounded-xl">
                <div class="px-5 py-4 font-semibold text-red-700">
                    ⚠️ Penanganan Terlambat
                </div>

                <div class="divide-y">
                    @foreach ($penangananTerlambat as $item)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <div class="font-medium text-gray-800">
                                    {{ $item->siswa->nama }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ str_replace('_', ' ', $item->status) }}
                                </div>
                            </div>
                            <a href="{{ route('penanganan.siswa', $item->id) }}" class="text-red-600 hover:underline">
                                Tindak Lanjuti
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ================= SELESAI + RATING ================= --}}
        <div class="bg-white rounded-xl shadow border">
            <div class="px-5 py-4 border-b font-semibold text-gray-700">
                Penanganan Selesai & Respon Wali
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Siswa</th>
                            <th class="px-4 py-3 text-left">Hasil</th>
                            <th class="px-4 py-3 text-left">Respon</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($penangananSelesai as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $item->siswa->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-3 capitalize">
                                    {{ $item->hasil ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($item->rating)
                                        <div class="flex items-center space-x-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $item->rating)
                                                    <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.95a1 1 0 00.95.69h4.15c.969 0 1.371 1.24.588 1.81l-3.36 2.44a1 1 0 00-.364 1.118l1.286 3.95c.3.921-.755 1.688-1.54 1.118l-3.36-2.44a1 1 0 00-1.175 0l-3.36 2.44c-.784.57-1.838-.197-1.54-1.118l1.286-3.95a1 1 0 00-.364-1.118L2.025 9.377c-.783-.57-.38-1.81.588-1.81h4.15a1 1 0 00.95-.69l1.286-3.95z" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.95a1 1 0 00.95.69h4.15c.969 0 1.371 1.24.588 1.81l-3.36 2.44a1 1 0 00-.364 1.118l1.286 3.95c.3.921-.755 1.688-1.54 1.118l-3.36-2.44a1 1 0 00-1.175 0l-3.36 2.44c-.784.57-1.838-.197-1.54-1.118l1.286-3.95a1 1 0 00-.364-1.118L2.025 9.377c-.783-.57-.38-1.81.588-1.81h4.15a1 1 0 00.95-.69l1.286-3.95z" />
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">
                                            Belum dinilai
                                        </span>
                                    @endif

                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $item->catatan ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada penanganan selesai
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= STATISTIK & CATATAN ================= --}}
        <div class="grid md:grid-cols-2 gap-6">

            <div class="bg-white rounded-xl shadow border p-5">
                <div class="font-semibold text-gray-700 mb-4">
                    Statistik Respon Wali
                </div>
                <ul class="text-sm space-y-2 text-gray-600">
                    <li>⭐ Rata-rata respon: <b>{{ $statistikRespon['rata_rata'] }}</b></li>
                    <li>📊 Total dinilai: <b>{{ $statistikRespon['total_dinilai'] }}</b></li>
                    <li>🟢 Responsif (≥4): <b>{{ $statistikRespon['responsif'] }}</b></li>
                    <li>🔴 Kurang responsif (≤2): <b>{{ $statistikRespon['kurang_responsif'] }}</b></li>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow border p-5">
                <div class="font-semibold text-gray-700 mb-4">
                    Catatan Respon Wali Terbaru
                </div>

                <div class="space-y-3 text-sm">
                    @forelse ($catatanTerbaru as $item)
                        <div class="border rounded-lg p-3">
                            <div class="font-bold text-gray-600 capitalize">WALI {{ $item->siswa->nama }}</div>

                            @if ($item->rating)
                                <div class="flex items-center space-x-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $item->rating)
                                            <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.95a1 1 0 00.95.69h4.15c.969 0 1.371 1.24.588 1.81l-3.36 2.44a1 1 0 00-.364 1.118l1.286 3.95c.3.921-.755 1.688-1.54 1.118l-3.36-2.44a1 1 0 00-1.175 0l-3.36 2.44c-.784.57-1.838-.197-1.54-1.118l1.286-3.95a1 1 0 00-.364-1.118L2.025 9.377c-.783-.57-.38-1.81.588-1.81h4.15a1 1 0 00.95-.69l1.286-3.95z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.95a1 1 0 00.95.69h4.15c.969 0 1.371 1.24.588 1.81l-3.36 2.44a1 1 0 00-.364 1.118l1.286 3.95c.3.921-.755 1.688-1.54 1.118l-3.36-2.44a1 1 0 00-1.175 0l-3.36 2.44c-.784.57-1.838-.197-1.54-1.118l1.286-3.95a1 1 0 00-.364-1.118L2.025 9.377c-.783-.57-.38-1.81.588-1.81h4.15a1 1 0 00.95-.69l1.286-3.95z" />
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">
                                    Belum dinilai
                                </span>
                            @endif

                            <p class="text-gray-600 mt-1">
                                {{ $item->catatan }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada catatan</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
@endsection
