@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Judul Halaman -->
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8">Laporan Aktivitas Petugas / Bendahara</h1>

        <!-- Form Filter -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <form method="GET" action="{{ route('admin.laporan.petugas') }}" class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Petugas</label>
                    <select name="petugas_id"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">Semua Petugas</option>
                        @foreach ($petugasList as $p)
                            <option value="{{ $p->id }}" {{ $petugasId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->roles->pluck('name')->join(', ') }} {{ $p->lembaga }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full md:w-auto bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-2.5 rounded-xl hover:from-blue-700 hover:to-blue-800 transition shadow-md font-medium">
                        Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistik Ringkasan -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
            <!-- Total Penanganan -->
            <div
                class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-4 flex items-center space-x-3 hover:shadow-md transition">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Penanganan</p>
                    <p class="text-xl font-bold text-gray-800">{{ $totalPenanganan }}</p>
                </div>
            </div>
            <!-- Selesai -->
            <div
                class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-4 flex items-center space-x-3 hover:shadow-md transition">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Selesai</p>
                    <p class="text-xl font-bold text-gray-800">{{ $selesai }}</p>
                </div>
            </div>
            <!-- Menunggu Respon -->
            <div
                class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-4 flex items-center space-x-3 hover:shadow-md transition">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Menunggu Respon</p>
                    <p class="text-xl font-bold text-gray-800">{{ $menungguRespon }}</p>
                </div>
            </div>
            <!-- Menunggu Tindak Lanjut -->
            <div
                class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-4 flex items-center space-x-3 hover:shadow-md transition">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Menunggu Tindak Lanjut</p>
                    <p class="text-xl font-bold text-gray-800">{{ $menungguTindakLanjut }}</p>
                </div>
            </div>
            <!-- Rata-rata Rating -->
            <div
                class="bg-white rounded-xl shadow-sm border-l-4 border-indigo-500 p-4 flex items-center space-x-3 hover:shadow-md transition">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Rata-rata Rating</p>
                    <p class="text-xl font-bold text-gray-800">{{ number_format($ratingAvg, 1) }} / 5</p>
                </div>
            </div>
        </div>

        <!-- Breakdown Hasil Penanganan Selesai -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                Breakdown Hasil Penanganan Selesai
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @forelse($hasilBreakdown as $hasil => $count)
                    <div
                        class="bg-gray-50/80 p-4 rounded-xl text-center border border-gray-100 hover:bg-gray-50 transition">
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $hasil)) }}</span>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $count }}</p>
                    </div>
                @empty
                    <div class="col-span-full text-center py-6 text-gray-500 bg-gray-50/50 rounded-xl">
                        Belum ada data penanganan selesai pada periode ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Daftar Penanganan dalam Bentuk Card Responsif -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                Detail Penanganan
            </h3>
            @if ($penanganan->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach ($penanganan as $p)
                        <div
                            class="group bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-blue-200 transition-all duration-200">
                            <!-- Header Card: Tanggal & Status -->
                            <div class="flex justify-between items-start mb-3">
                                <span
                                    class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ $p->created_at->format('d/m/Y H:i') }}</span>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if ($p->status == 'selesai') bg-green-100 text-green-700
                                    @elseif($p->status == 'menunggu_respon') bg-yellow-100 text-yellow-700
                                    @elseif($p->status == 'menunggu_tindak_lanjut') bg-blue-100 text-blue-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ str_replace('_', ' ', $p->status) }}
                                </span>
                            </div>
                            <!-- Informasi Petugas & Siswa -->
                            <div class="space-y-2 mb-3">
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-16">Petugas</span>
                                    <span class="font-medium text-gray-800 truncate">{{ $p->petugas->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-16">Siswa</span>
                                    <span class="font-medium text-gray-800 truncate">{{ $p->siswa->nama ?? '-' }}</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-16">Hasil</span>
                                    <span class="font-medium text-gray-800 truncate">{{ $p->hasil ?? '-' }}</span>
                                </div>
                            </div>
                            <!-- Rating -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Rating</span>
                                @if ($p->rating)
                                    <div class="flex items-center">
                                        <span class="text-yellow-500 font-bold">{{ $p->rating }}</span>
                                        <span class="text-gray-400 text-sm ml-1">/5</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 text-gray-500 bg-gray-50/50 rounded-xl">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500">Tidak ada data penanganan pada periode ini.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
