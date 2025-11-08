@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Daftar Petugas</h2>
    <a href="{{ route('admin.petugas.create') }}" class="btn btn-primary mb-3">+ Tambah Petugas</a>

    <table class="table-auto w-full">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Lembaga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($petugas as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->lembaga }}</td>
                    <td>{{ $p->deleted_at ? 'Nonaktif' : 'Aktif' }}</td>
                    <td>
                        @if (!$p->deleted_at)
                            <a href="{{ route('admin.petugas.edit', $p->id) }}" class="text-blue-500">Edit</a> |
                            <form action="{{ route('admin.petugas.destroy', $p->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="text-red-500"
                                    onclick="return confirm('Nonaktifkan petugas ini?')">Hapus</button>
                            </form>
                        @else
                            <form action="{{ route('admin.petugas.restore', $p->id) }}" method="POST"
                                style="display:inline">
                                @csrf
                                <button class="text-green-600">Pulihkan</button>
                            </form>
                            |
                            <form action="{{ route('admin.petugas.forceDelete', $p->id) }}" method="POST"
                                style="display:inline">
                                @csrf @method('DELETE')
                                <button class="text-red-700" onclick="return confirm('Hapus permanen?')">Hapus
                                    Permanen</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
