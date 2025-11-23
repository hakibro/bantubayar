@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="p-6">

        <h1 class="text-2xl font-bold mb-6">Assign Siswa ke Petugas</h1>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- Daftar Siswa --}}
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold mb-4">Daftar Siswa</h2>

            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2 text-left">Nama</th>
                        <th class="border p-2 text-left">NIS</th>
                        <th class="border p-2 text-left">Petugas Saat Ini</th>
                        <th class="border p-2">Assign Petugas</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($siswa as $item)
                        <tr>
                            <td class="border p-2">{{ $item->nama }}</td>
                            <td class="border p-2">{{ $item->nis }}</td>
                            <td class="border p-2">
                                @php
                                    $assigned = $item->petugas->first();
                                @endphp

                                @if ($assigned)
                                    <span class="text-blue-600 font-semibold">
                                        {{ $assigned->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Belum ada petugas</span>
                                @endif
                            </td>

                            <td class="border p-2">
                                <form action="{{ route('admin.assign.store') }}" method="POST"
                                    class="flex items-center space-x-2">
                                    @csrf

                                    <input type="hidden" name="siswa_id" value="{{ $item->id }}">

                                    <select name="petugas_id" class="border rounded p-1 w-48">
                                        <option value="">— pilih petugas —</option>
                                        @foreach ($petugas as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>

                                    <button class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Assign
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>
@endsection
