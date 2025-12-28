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
                <div x-data="{ open: false }" class="bg-white border rounded-lg shadow-sm p-4 hover:shadow transition">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-start">
                        <div class="space-y-0.5">
                            <p class="text-xs text-gray-500">
                                {{ $p->created_at->format('d M Y H:i') }}
                            </p>
                            <p class="font-semibold text-gray-800">
                                Penanganan via {{ ucfirst($p->jenis_penanganan) }}
                            </p>
                        </div>

                        <span
                            class="px-2 py-1 rounded text-xs font-medium
                    @if ($p->status === 'selesai') bg-green-100 text-green-700
                    @elseif ($p->status === 'proses') bg-yellow-100 text-yellow-700
                    @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($p->status) }}
                        </span>
                    </div>

                    {{-- RINGKASAN TOTAL --}}
                    <div class="mt-3 text-sm text-red-700 space-y-1">
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

                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-gray-700 min-w-[88px]">
                                    {{ $periode }}
                                </span>
                                <span class="font-semibold">
                                    Rp {{ number_format($totalPeriode, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- TOGGLE --}}
                    <button @click="open = !open" class="mt-3 text-xs text-blue-600 hover:underline focus:outline-none">
                        <span x-show="!open">Lihat detail</span>
                        <span x-show="open">Sembunyikan detail</span>
                    </button>

                    {{-- DETAIL --}}
                    <div x-show="open" x-transition class="mt-3 border-t pt-3 text-sm space-y-4">

                        @foreach ($groupedByPeriode as $periode => $itemsPerPeriode)
                            <div class="space-y-2">
                                <p class="font-semibold text-gray-700">
                                    Periode {{ $periode }}
                                </p>

                                @foreach ($itemsPerPeriode as $jp)
                                    <div class="ml-3 space-y-1">
                                        <p class="font-medium text-gray-800">
                                            {{ $jp['category_name'] }}
                                        </p>

                                        <ul class="list-disc ml-5 text-xs text-gray-600 space-y-1">
                                            @foreach ($jp['items'] as $item)
                                                <li class="flex items-center gap-3">
                                                    <span class="flex-1">
                                                        {{ $item['unit_name'] }}
                                                    </span>
                                                    <span class="text-red-600 font-medium whitespace-nowrap">
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
                    <div class="mt-4 pt-3 border-t flex justify-between items-center text-sm">
                        <div class="text-gray-600">
                            <span class="text-gray-500">Petugas:</span>
                            <span class="font-medium">{{ $p->petugas->name }}</span>
                        </div>

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
