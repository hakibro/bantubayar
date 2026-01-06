@extends('layouts.dashboard')
@section('title', 'Riwayat Penanganan')

@section('content')
    <div class="p-6">



        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold mb-2">
                {{ $siswa->nama }}
                <a href="{{ route('bendahara.siswa.show', $siswa->id) }}"
                    class="inline-flex items-center gap-2 text-sm font-medium
          text-indigo-600 hover:text-indigo-700
          bg-indigo-50 hover:bg-indigo-100
          px-4 py-2 rounded-lg
          transition">
                    <i class="fa-solid fa-eye"></i>
                    Detail Siswa
                </a>
            </h2>

            <a href="{{ route('penanganan.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium
          text-indigo-600 hover:text-indigo-700
          bg-indigo-50 hover:bg-indigo-100
          px-4 py-2 rounded-lg
          transition">
                <i class="fa-solid fa-arrow-left"></i>
                Daftar Penanganan Siswa
            </a>
        </div>

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
                            @if ($p->rating)
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= $p->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                @endfor
                            @else
                                <span class="text-sm text-gray-500">Tidak ada Rating</span>
                            @endif

                            @if ($p->catatan)
                                <div class="mt-1 text-sm text-gray-600">
                                    Catatan: {{ $p->catatan }}
                                </div>
                            @endif
                            <br>

                            <span
                                class="px-2 py-1 rounded text-xs font-medium
                    @if ($p->status === 'selesai') bg-green-100 text-green-700
                    @else ($p->status !== 'selesai') bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($p->status) }}
                            </span>
                            <span
                                class="px-2 py-1 rounded text-xs font-medium
                    @if ($p->hasil === 'lunas') bg-green-100 text-green-700
                    @elseif ($p->hasil === 'isi_saldo') bg-blue-100 text-blue-700 @elseif ($p->hasil === 'rekomendasi') bg-yellow-100 text-yellow-700 @elseif ($p->hasil === 'tidak_ada_respon') bg-red-100 text-red-700 @endif">
                                {{ ucfirst($p->hasil) }}
                            </span>

                            {{-- tampilkan bukti pembayaran jika ada --}}
                            @if ($p->bukti_pembayaran)
                                <div class="mt-1">
                                    <a href="{{ Storage::url($p->bukti_pembayaran) }}" target="_blank"
                                        class="text-sm text-blue-600 hover:underline">
                                        Lihat Bukti Pembayaran
                                    </a>
                                </div>
                            @endif

                            {{-- tampilkan tanggal rekomendasi jika ada --}}
                            @if ($p->tanggal_rekom)
                                <div class="mt-1 text-sm text-gray-600">
                                    Tanggal Rekomendasi: {{ \Carbon\Carbon::parse($p->tanggal_rekom)->format('d M Y') }}
                                </div>
                            @endif

                        </div>
                        <p class="text-xs text-gray-500">
                            Dibuat {{ $p->created_at->diffForHumans() }}
                            <br>
                            Diperbarui {{ $p->updated_at->diffForHumans() }}
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

                        <a href="{{ route('penanganan.edit', $p->id) }}" class="text-blue-600 hover:underline">
                            Edit Penanganan
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
