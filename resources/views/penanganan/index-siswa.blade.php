@extends('layouts.dashboard')

@section('content')
    <div class="p-6">

        <h2 class="text-xl font-bold mb-2">
            Riwayat Penanganan – {{ $siswa->nama }}
        </h2>

        @if ($bolehBuatPenanganan)
            <a href="{{ route('penanganan.create', $siswa->id) }}"
                class="px-3 py-1 bg-blue-600 text-white rounded inline-block mb-4">
                Buat Penanganan Baru
            </a>
        @else
            <div class="mb-4 px-3 py-2 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded">
                Penanganan sebelumnya <strong>belum selesai</strong>.
                <br>
                Status terakhir:
                <span class="font-semibold capitalize">
                    {{ str_replace('_', ' ', $penangananTerakhir->status) }}
                </span>
            </div>
        @endif

        <div class="space-y-4">

            @forelse ($penanganan as $p)
                <div x-data="{ open: false }"
                    class="bg-white border rounded-lg shadow-sm hover:shadow transition p-4 space-y-4">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-gray-800">
                                Penanganan via {{ ucfirst($p->jenis_penanganan) }}
                            </h3>
                            <span
                                class="px-2 py-1 rounded text-xs font-medium
                    @if ($p->status === 'selesai') bg-green-100 text-green-700
                    @else ($p->status !== 'selesai') bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($p->status) }}
                            </span>

                        </div>
                        <p class="text-xs text-gray-500">
                            {{ $p->created_at->format('d M Y H:i') }}
                            <br>
                            {{ $p->created_at->diffForHumans() }}
                        </p>

                    </div>

                    {{-- SUMMARY --}}
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase">
                            Ringkasan Tunggakan
                        </p>

                        @php
                            $groupedByPeriode = collect($p->jenis_pembayaran)->groupBy('periode');
                        @endphp

                        @foreach ($groupedByPeriode as $periode => $itemsPerPeriode)
                            @php
                                $totalPeriode = 0;
                                foreach ($itemsPerPeriode as $jp) {
                                    $totalPeriode += collect($jp['items'] ?? [])->sum(
                                        fn($i) => $i['remaining_balance'] ?? 0,
                                    );
                                }
                            @endphp

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700 font-medium">
                                    Periode {{ $periode }}
                                </span>
                                <span class="font-semibold text-red-600">
                                    Rp {{ number_format($totalPeriode, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- TOGGLE --}}
                    <button @click="open = !open" class="text-xs text-blue-600 hover:underline focus:outline-none">
                        <span x-show="!open">Lihat detail pembayaran</span>
                        <span x-show="open">Sembunyikan detail</span>
                    </button>

                    {{-- DETAIL --}}
                    <div x-show="open" x-transition class="border-t pt-4 space-y-4 text-sm">

                        @foreach ($groupedByPeriode as $periode => $itemsPerPeriode)
                            <div class="space-y-3">
                                <p class="font-semibold text-gray-700">
                                    Periode {{ $periode }}
                                </p>

                                @foreach ($itemsPerPeriode as $jp)
                                    <div class="pl-3 border-l space-y-1">
                                        <p class="font-medium text-gray-800">
                                            {{ $jp['category_name'] }}
                                        </p>

                                        <ul class="text-xs text-gray-600 space-y-1">
                                            @foreach ($jp['items'] as $item)
                                                <li class="flex justify-between">
                                                    <span>{{ $item['unit_name'] }}</span>
                                                    <span class="font-medium text-red-600">
                                                        Rp {{ number_format($item['remaining_balance'], 0, ',', '.') }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    {{-- FOOTER --}}
                    <div class="pt-3 border-t flex justify-between items-center text-xs text-gray-600">
                        <span>
                            Petugas: <strong class="text-gray-800">{{ $p->petugas->name }}</strong>
                        </span>

                        <a href="#" class="text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white border rounded-lg p-6 text-center text-gray-500">
                    Belum ada riwayat penanganan.
                </div>
            @endforelse

        </div>







    </div>
@endsection
