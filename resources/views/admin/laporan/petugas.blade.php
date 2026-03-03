@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">Laporan Aktivitas Petugas / Bendahara</h1>

        <!-- Form Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.laporan.petugas') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Petugas</label>
                    <select name="petugas_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Semua Petugas</option>
                        @foreach ($petugasList as $p)
                            <option value="{{ $p->id }}" {{ $petugasId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->roles->pluck('name')->join(', ') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistik Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Total Penanganan</p>
                <p class="text-2xl font-bold">{{ $totalPenanganan }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Selesai</p>
                <p class="text-2xl font-bold">{{ $selesai }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
                <p class="text-sm text-gray-500">Menunggu Respon</p>
                <p class="text-2xl font-bold">{{ $menungguRespon }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Menunggu Tindak Lanjut</p>
                <p class="text-2xl font-bold">{{ $menungguTindakLanjut }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                <p class="text-sm text-gray-500">Rata-rata Rating</p>
                <p class="text-2xl font-bold">{{ $ratingAvg }} / 5</p>
            </div>
        </div>

        <!-- Breakdown Hasil -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-lg mb-4">Breakdown Hasil Penanganan Selesai</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($hasilBreakdown as $hasil => $count)
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $hasil)) }}</span>
                        <p class="text-xl font-bold">{{ $count }}</p>
                    </div>
                @endforeach
                @if ($hasilBreakdown->isEmpty())
                    <p class="text-gray-500 col-span-4">Belum ada data penanganan selesai pada periode ini.</p>
                @endif
            </div>
        </div>

        <!-- Tabel Detail Penanganan -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="font-semibold text-lg">Detail Penanganan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hasil</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($penanganan as $p)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $p->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $p->petugas->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $p->siswa->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs
                                @if ($p->status == 'selesai') bg-green-100 text-green-800
                                @elseif($p->status == 'menunggu_respon') bg-yellow-100 text-yellow-800
                                @elseif($p->status == 'menunggu_tindak_lanjut') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $p->hasil ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($p->rating)
                                        {{ $p->rating }} / 5
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data penanganan
                                    pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
