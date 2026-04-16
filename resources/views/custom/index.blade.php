@extends('layouts.container')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 flex justify-between items-center">
                <h1 class="text-white font-bold text-xl">Daftar Siswa (Periode < 20242025) - Tunggakan & Kelebihan Bayar</h1>
                        <a href="{{ route('siswa.belum-lunas.export', request()->query()) }}"
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export XLSX
                        </a>
            </div>

            <div class="p-6">
                <!-- Form Filter -->
                <form method="GET" action="{{ route('siswa.belum-lunas.index') }}" class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Cari (Nama / ID)</label>
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Unit Formal</label>
                            <select name="unit_formal" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Semua</option>
                                @foreach ($unitFormalList as $uf)
                                    <option value="{{ $uf }}"
                                        {{ request('unit_formal') == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Asrama Pondok</label>
                            <select name="asrama_pondok" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Semua</option>
                                @foreach ($asramaPondokList as $ap)
                                    <option value="{{ $ap }}"
                                        {{ request('asrama_pondok') == $ap ? 'selected' : '' }}>{{ $ap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tingkat Diniyah</label>
                            <select name="tingkat_diniyah" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Semua</option>
                                @foreach ($tingkatDiniyahList as $td)
                                    <option value="{{ $td }}"
                                        {{ request('tingkat_diniyah') == $td ? 'selected' : '' }}>{{ $td }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg">Filter</button>
                            <a href="{{ route('siswa.belum-lunas.index') }}"
                                class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg text-center">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Tabel -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Person</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Formal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas Formal
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asrama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tingkat Diniyah
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Tunggakan
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Kelebihan
                                    Bayar</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail (Per
                                    Periode)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($siswaList as $siswa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">{{ $siswa->idperson }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $siswa->nama }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->UnitFormal }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->KelasFormal }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->AsramaPondok }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->TingkatDiniyah }}</td>
                                    <td class="px-4 py-3 text-sm text-red-600 font-bold">
                                        Rp {{ number_format($siswa->total_tunggakan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-green-600 font-bold">
                                        Rp {{ number_format($siswa->total_overpaid, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <button onclick="openDetailModal({{ $siswa->id }})"
                                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-lg">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-6 text-gray-500">Tidak ada data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $siswaList->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail (tunggakan + overpaid) -->
    @foreach ($siswaList as $siswa)
        <div id="detailModal{{ $siswa->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="closeDetailModal({{ $siswa->id }})"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                        <h3 class="text-lg font-bold">Detail Saldo per Periode - {{ $siswa->nama }}
                            ({{ $siswa->idperson }})
                        </h3>
                        <button onclick="closeDetailModal({{ $siswa->id }})" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        @php $grouped = $siswa->tunggakan_per_periode ?? []; @endphp
                        @forelse($grouped as $periode => $kategoriItems)
                            <div class="mb-6 border rounded-lg overflow-hidden">
                                <div class="bg-gray-800 text-white px-4 py-2">
                                    <strong>Periode: {{ $periode }}</strong>
                                </div>
                                <div class="p-4">
                                    @foreach ($kategoriItems as $item)
                                        @php
                                            $isOverpaid = ($item['type'] ?? 'tunggakan') === 'overpaid';
                                            $bgColor = $isOverpaid
                                                ? 'bg-green-50 border-green-400'
                                                : 'bg-yellow-50 border-yellow-400';
                                            $headerBg = $isOverpaid ? 'bg-green-100' : 'bg-yellow-100';
                                        @endphp
                                        <div class="{{ $bgColor }} border-l-4 rounded-lg mb-4 shadow-sm">
                                            <div class="{{ $headerBg }} px-4 py-2 flex justify-between items-center">
                                                <strong>{{ $item['category_name'] }}</strong>
                                                <span class="text-sm bg-gray-200 px-2 py-1 rounded">Kelas:
                                                    {{ $item['kelas_info'] ?? '-' }}</span>
                                            </div>
                                            <div class="p-4">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full text-sm">
                                                        <!-- Di dalam modal, bagian tabel item -->
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-3 py-2 text-left">Item</th>
                                                                <th>Tagihan</th> <!-- dari amount_tagihan -->
                                                                <th>Dibayar</th> <!-- dari amount_dibayar -->
                                                                <th>Sisa</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($item['items'] as $i)
                                                                <tr>
                                                                    <td class="px-3 py-2">{{ $i['unit_name'] }}</td>
                                                                    <td class="px-3 py-2">Rp
                                                                        {{ number_format($i['amount_tagihan'], 0, ',', '.') }}
                                                                    </td>
                                                                    <td class="px-3 py-2">Rp
                                                                        {{ number_format($i['amount_dibayar'], 0, ',', '.') }}
                                                                    </td>
                                                                    <td
                                                                        class="px-3 py-2 {{ $isOverpaid ? 'text-green-600' : 'text-red-600' }}">
                                                                        Rp
                                                                        {{ number_format(abs($i['remaining_balance']), 0, ',', '.') }}
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        <span class="...">
                                                                            {{ $i['payment_status'] == 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="mt-3 bg-gray-100 p-2 rounded text-sm">
                                                    <strong>Ringkasan Kategori:</strong> Tagihan Rp
                                                    {{ number_format($item['summary']['total_billed'], 0, ',', '.') }},
                                                    Dibayar Rp
                                                    {{ number_format($item['summary']['total_paid'], 0, ',', '.') }},
                                                    <span class="{{ $isOverpaid ? 'text-green-600' : 'text-red-600' }}">
                                                        Sisa Rp {{ number_format(abs($item['sisa']), 0, ',', '.') }}
                                                        ({{ $isOverpaid ? 'Kelebihan Bayar' : 'Tunggakan' }})
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada data tunggakan atau kelebihan bayar.</p>
                        @endforelse
                    </div>
                    <div class="sticky bottom-0 bg-gray-50 border-t px-6 py-3 flex justify-end">
                        <button onclick="closeDetailModal({{ $siswa->id }})"
                            class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-lg">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function openDetailModal(id) {
            const modal = document.getElementById('detailModal' + id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDetailModal(id) {
            const modal = document.getElementById('detailModal' + id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') document.querySelectorAll('[id^="detailModal"]').forEach(m => {
                if (!m.classList.contains('hidden')) {
                    m.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
@endsection
