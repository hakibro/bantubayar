@extends('layouts.dashboard')

@section('title', 'Manage Siswa')

@section('content')
    <div class="max-w-5xl mx-auto p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $siswa->nama }}</h1>
                <p class="text-gray-500">ID Person: {{ $siswa->idperson }}</p>
            </div>
            <a href="{{ route('admin.siswa.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        {{-- DATA SISWA --}}
        <div class="bg-white shadow p-5 rounded mb-6">
            <h2 class="text-lg font-semibold mb-3">Informasi Siswa</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <strong>Nama:</strong>
                    <p>{{ $siswa->nama }}</p>
                </div>
                <div>
                    <strong>Lembaga Formal:</strong>
                    <p>{{ $siswa->unit_formal ?? '-' }} ({{ $siswa->kelas_formal ?? '-' }})</p>
                </div>
                <div>
                    <strong>Asrama:</strong>
                    <p>{{ $siswa->AsramaPondok ?? '-' }} - {{ $siswa->KamarPondok ?? '-' }}</p>
                </div>
                <div>
                    <strong>Status Pembayaran:</strong>
                    <p class="{{ $siswa->statusLunas?->is_lunas ? 'text-green-600' : 'text-red-600' }} font-semibold">
                        {{ $siswa->statusLunas?->is_lunas ? 'Lunas' : 'Belum Lunas' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- RINGKASAN PER PERIODE --}}
        <div class="bg-white shadow p-5 rounded mb-6">
            <h2 class="text-lg font-semibold mb-4">Ringkasan Per Periode</h2>
            @forelse ($summary as $sum)
                <div class="border rounded mb-3 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-blue-700">Periode {{ $sum->idperiode }}</span>
                        <span class="{{ $sum->lunas ? 'text-green-600' : 'text-red-600' }} text-sm font-bold">
                            {{ $sum->lunas ? 'Lunas' : 'Belum Lunas' }}
                        </span>
                    </div>
                    @if ($sum->kelas_history)
                        <p class="text-xs text-gray-500 mb-2">{{ $sum->kelas_history }}</p>
                    @endif
                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <strong>Tagihan:</strong>
                            <p>Rp {{ number_format($sum->total_kredit, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <strong>Dibayar:</strong>
                            <p>Rp {{ number_format($sum->total_debet, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <strong>Sisa:</strong>
                            <p class="{{ $sum->sisa_tagihan > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format($sum->sisa_tagihan, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">Tidak ada data pembayaran.</p>
            @endforelse
        </div>

        {{-- DETAIL TUNGGAKAN --}}
        @if (count($belumLunas) > 0)
            <div class="bg-white shadow p-5 rounded">
                <h2 class="text-lg font-semibold mb-4">Detail Belum Lunas</h2>
                @php $grouped = collect($belumLunas)->groupBy('idperiode'); @endphp
                @foreach ($grouped as $periode => $items)
                    <div class="mb-4">
                        <h3 class="text-blue-600 font-semibold mb-2">Periode {{ $periode }}</h3>
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left">Kategori</th>
                                    <th class="p-2 text-left">Unit</th>
                                    <th class="p-2 text-right">Tagihan</th>
                                    <th class="p-2 text-right">Dibayar</th>
                                    <th class="p-2 text-right">Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr class="border-b">
                                        <td class="p-2">{{ $item->judul }}</td>
                                        <td class="p-2">{{ $item->nama_unit }}</td>
                                        <td class="p-2 text-right">{{ number_format($item->jml_kredit, 0, ',', '.') }}</td>
                                        <td class="p-2 text-right">{{ number_format($item->jml_debet, 0, ',', '.') }}</td>
                                        <td class="p-2 text-right text-red-600 font-semibold">
                                            {{ number_format($item->selisih, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
@endsection
