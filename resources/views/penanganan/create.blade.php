@extends('layouts.dashboard')

@section('title', 'Tambah Penanganan')

@section('content')
    <div class="p-6">

        <h2 class="text-xl font-bold mb-4">
            Penanganan Untuk: {{ $siswa->nama }}
        </h2>

        <form action="{{ route('penanganan.store') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="id_siswa" value="{{ $siswa->id }}">

            {{-- Jenis Penanganan --}}
            <div>
                <label class="block font-medium mb-1">Jenis Penanganan</label>
                <select name="jenis_penanganan" class="w-full border rounded p-2">
                    <option value="penagihan">Penagihan</option>
                    <option value="pendekatan">Pendekatan</option>
                    <option value="pertemuan">Pertemuan Orang Tua</option>
                </select>
            </div>

            {{-- Daftar Pembayaran Belum Lunas --}}
            <div class="border rounded p-4 bg-white">
                <h3 class="font-semibold mb-3">Daftar Pembayaran Belum Lunas</h3>

                @foreach ($kategoriBelumLunas as $periode => $items)
                    <div class="mb-4 p-3 border rounded bg-gray-50">
                        <p class="font-bold mb-2">Periode: {{ $periode }}</p>

                        @if (count($items) === 0)
                            <p class="text-sm text-gray-500">Tidak ada tunggakan.</p>
                        @else
                            <ul class="list-disc ml-5">
                                @foreach ($items as $item)
                                    @php
                                        $kategori = array_key_first($item);
                                        $nominal = $item[$kategori];
                                    @endphp
                                    <li>{{ $kategori }} — <b>Rp {{ number_format($nominal, 0, ',', '.') }}</b></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block font-medium mb-1">Catatan</label>
                <textarea name="catatan" class="w-full border rounded p-2" rows="3"></textarea>
            </div>

            {{-- Hasil --}}
            <div>
                <label class="block font-medium mb-1">Hasil</label>
                <textarea name="hasil" class="w-full border rounded p-2" rows="3"></textarea>
            </div>

            {{-- Tanggal Rekomendasi --}}
            <div>
                <label class="block font-medium mb-1">Tanggal Rekomendasi</label>
                <input type="date" name="tanggal_rekom" class="w-full border rounded p-2">
            </div>

            {{-- Status --}}
            <div>
                <label class="block font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded p-2">
                    <option value="belum">Belum</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                Simpan Penanganan
            </button>
        </form>

    </div>
@endsection
