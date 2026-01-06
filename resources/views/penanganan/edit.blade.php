@extends('layouts.dashboard')

@section('title', 'Edit Penanganan')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- DATA SISWA --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-semibold mb-3">Data Siswa</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama</p>
                    <p class="font-medium">{{ $siswa->nama }}</p>
                </div>
                <div>
                    <p class="text-gray-500">ID Siswa</p>
                    <p class="font-medium">{{ $siswa->idperson }}</p>
                </div>
                <div>
                    <p class="text-gray-500">No. HP</p>
                    <p class="font-medium">{{ $siswa->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- TUNGGAKAN (READ ONLY) --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-semibold mb-4">Tunggakan Pembayaran</h2>

            @php
                $groupedByPeriode = collect($kategoriBelumLunas)->groupBy('periode');
            @endphp

            <div class="space-y-6">
                @forelse ($groupedByPeriode as $periode => $itemsPerPeriode)
                    <div class="border rounded-lg p-4">
                        <h3 class="text-blue-600 font-semibold mb-2">
                            Periode {{ $periode }}
                        </h3>

                        <div class="space-y-3">
                            @foreach ($itemsPerPeriode as $item)
                                <div>
                                    <p class="font-medium mb-2">
                                        {{ $item['category_name'] }}
                                    </p>

                                    <ul class="space-y-1 text-sm">
                                        @foreach ($item['items'] as $detail)
                                            <li class="flex justify-between">
                                                <span>{{ $detail['unit_name'] ?? '-' }}</span>
                                                <span class="text-red-600 font-semibold">
                                                    Rp {{ number_format($detail['remaining_balance'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada tunggakan aktif.</p>
                @endforelse
            </div>
        </div>

        {{-- FORM UPDATE PENANGANAN --}}
        <form action="{{ route('penanganan.update', $penanganan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow p-5 space-y-4">
                <h2 class="text-lg font-semibold">Edit Penanganan</h2>

                {{-- JENIS PENANGANAN --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jenis Penanganan
                    </label>
                    <select name="jenis_penanganan" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                        @foreach (['chat', 'telepon', 'telepon_ulang', 'visit'] as $jenis)
                            <option value="{{ $jenis }}" @selected($penanganan->jenis_penanganan === $jenis)>
                                {{ ucfirst(str_replace('_', ' ', $jenis)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- CATATAN --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Catatan
                    </label>
                    <textarea name="catatan" rows="4"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" placeholder="Catatan tambahan...">{{ old('catatan', $penanganan->catatan) }}</textarea>
                </div>

                {{-- HASIL --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Hasil Penanganan
                    </label>
                    <select name="hasil"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                        <option value="">-- Pilih hasil --</option>
                        <option value="lunas" @selected($penanganan->hasil === 'lunas')>Lunas</option>
                        <option value="tidak_ada_respon" @selected($penanganan->hasil === 'tidak_ada_respon')>
                            Tidak Ada Respon
                        </option>
                        <option value="rekom_isi_saldo" @selected($penanganan->hasil === 'rekom_isi_saldo')>
                            Rekomendasi Isi Saldo
                        </option>
                        <option value="rekom_tidak_isi_saldo" @selected($penanganan->hasil === 'rekom_tidak_isi_saldo')>
                            Rekomendasi Tidak Isi Saldo
                        </option>
                    </select>
                </div>

                {{-- TANGGAL REKOMENDASI --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tanggal Rekomendasi
                    </label>
                    <input type="date" name="tanggal_rekom"
                        value="{{ old('tanggal_rekom', $penanganan->tanggal_rekom) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                </div>

                {{-- STATUS (READ ONLY) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Status
                    </label>
                    <input type="text" readonly value="{{ str_replace('_', ' ', ucfirst($penanganan->status)) }}"
                        class="w-full bg-gray-100 border rounded-lg px-3 py-2 text-sm text-gray-600">
                </div>

                {{-- ACTION --}}
                <div class="flex justify-between items-center pt-4">
                    <a href="{{ route('penanganan.siswa', $siswa->id) }}"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">
                        Kembali
                    </a>

                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Penanganan
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
