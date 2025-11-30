@extends('layouts.dashboard')

@section('content')
    <div class="p-6">

        <h2 class="text-xl font-bold mb-2">
            Riwayat Penanganan – {{ $siswa->nama }}
        </h2>

        <a href="{{ route('penanganan.create', $siswa->id) }}"
            class="px-3 py-1 bg-blue-600 text-white rounded inline-block mb-4">
            Buat Penanganan Baru
        </a>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border text-left">Tanggal</th>
                    <th class="p-2 border text-left">Jenis</th>
                    <th class="p-2 border text-left">Pembayaran</th>
                    <th class="p-2 border text-left">Petugas</th>
                    <th class="p-2 border text-left">Status</th>
                    <th class="p-2 border text-left">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($penanganan as $p)
                    <tr>
                        <td class="border p-2">{{ $p->created_at->format('d M Y H:i') }}</td>
                        <td class="border p-2">{{ ucfirst($p->jenis_penanganan) }}</td>
                        <td class="border p-2">
                            @foreach ($p->jenis_pembayaran as $jp)
                                <span class="bg-gray-200 px-2 py-1 rounded text-xs">{{ $jp }}</span>
                            @endforeach
                        </td>
                        <td class="border p-2">{{ $p->petugas->name }}</td>
                        <td class="border p-2">{{ ucfirst($p->status) }}</td>
                        <td class="border p-2">
                            <a href="#" class="text-blue-600">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border p-4 text-center text-gray-500">
                            Belum ada riwayat penanganan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
@endsection
