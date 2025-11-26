@extends('layouts.dashboard')

@section('title', 'Show Siswa')

@section('content')
    <div class="max-w-5xl mx-auto p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Siswa</h1>
                <p class="text-gray-500">ID Person: {{ $siswa->idperson }}</p>
            </div>

            <!-- TODO: Buat sync pembayaran single siswa -->

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
                    <strong>Kelas:</strong>
                    <p>{{ $siswa->kelas ?? '-' }}</p>
                </div>
                <div>
                    <strong>Unit:</strong>
                    <p>{{ $siswa->unit ?? '-' }}</p>
                </div>
                <div>
                    <strong>Terakhir Update Pembayaran:</strong>
                    <p>{{ $siswa->pembayaran->max('updated_at')?->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- PEMBAYARAN --}}
        <div class="bg-white shadow p-5 rounded">
            <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h2>

            @foreach ($siswa->pembayaran as $pay)
                <div x-data="{ open: false }" class="border rounded mb-4">

                    {{-- HEADER PERIODE --}}
                    <button @click="open = !open"
                        class="w-full flex justify-between p-4 bg-gray-100 hover:bg-gray-200 transition">
                        <span class="font-semibold">
                            Periode: {{ $pay->periode }}
                        </span>

                        @php
                            $s = $pay->data['summary'];
                        @endphp

                        <span class="{{ $s['fully_paid'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($s['total_paid']) }} / {{ number_format($s['total_billed']) }}
                        </span>
                    </button>

                    {{-- BODY --}}
                    <div x-show="open" class="p-4 space-y-4">

                        {{-- SUMMARY --}}
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <strong>Total Tagihan:</strong>
                                <p>{{ number_format($s['total_billed']) }}</p>
                            </div>
                            <div>
                                <strong>Total Bayar:</strong>
                                <p>{{ number_format($s['total_paid']) }}</p>
                            </div>
                            <div>
                                <strong>Sisa:</strong>
                                <p class="{{ $s['total_remaining'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($s['total_remaining']) }}
                                </p>
                            </div>
                        </div>

                        {{-- CATEGORIES --}}
                        @foreach ($pay->data['categories'] as $cat)
                            <div class="border rounded p-4">
                                <h3 class="font-semibold mb-2">{{ $cat['category_name'] }}</h3>

                                {{-- ITEMS --}}
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="p-2 text-left">Unit</th>
                                            <th class="p-2 text-left">Tagihan</th>
                                            <th class="p-2 text-left">Bayar</th>
                                            <th class="p-2 text-left">Sisa</th>
                                            <th class="p-2 text-left">Status</th>
                                            <th class="p-2 text-left">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cat['items'] as $item)
                                            <tr class="border-b">
                                                <td class="p-2">{{ $item['unit_name'] }}</td>
                                                <td class="p-2">{{ number_format($item['amount_billed']) }}</td>
                                                <td class="p-2">{{ number_format($item['amount_paid']) }}</td>
                                                <td class="p-2">{{ number_format($item['remaining_balance']) }}</td>
                                                <td class="p-2">
                                                    <span
                                                        class="px-2 py-1 text-xs rounded
                                                {{ $item['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                        {{ ucfirst($item['payment_status']) }}
                                                    </span>
                                                </td>
                                                <td class="p-2">{{ $item['journal_date'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        @endforeach

                    </div>

                </div>
            @endforeach

        </div>

    </div>
@endsection
