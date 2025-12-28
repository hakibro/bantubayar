@extends('layouts.dashboard')

@section('content')
    <div class="p-6">
        <h2 class="text-xl font-bold mb-4">Daftar Penanganan</h2>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Siswa</th>
                    <th class="p-2 border">Petugas</th>
                    <th class="p-2 border">Jenis</th>
                    <th class="p-2 border">Status</th>
                    <th class="p-2 border">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td class="border p-2">{{ $row->siswa->nama }}</td>
                        <td class="border p-2">{{ $row->petugas->name }}</td>
                        <td class="border p-2">{{ ucfirst($row->jenis_penanganan) }}</td>
                        <td class="border p-2">{{ ucfirst($row->status) }}</td>
                        <td class="border p-2">
                            <a href="{{ route('penanganan.siswa', $row->siswa->id) }}" class="text-blue-600">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $data->links() }}
    </div>
@endsection
