@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Data Petugas</h1>
                <p class="text-sm text-gray-500">Perbarui informasi petugas di bawah ini.</p>
            </div>
            <a href="{{ route('admin.petugas.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg shadow transition">
                ← Kembali
            </a>
        </div>

        <!-- Alert error -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Alert success -->
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white p-6 rounded-xl shadow-md max-w-xl mx-auto">
            <form action="{{ route('admin.petugas.update', $petugas->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $petugas->name) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Masukkan nama lengkap" required>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $petugas->email) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="nama@email.com" required>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password (opsional)</label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Isi jika ingin mengubah password">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti password.</p>
                </div>
                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi
                        Password (opsional)</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Ulangi password">
                </div>

                <!-- Lembaga -->
                <div>
                    <label for="lembaga" class="block text-sm font-medium text-gray-700 mb-1">
                        Lembaga
                    </label>

                    <select id="lembaga" name="lembaga"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">

                        <option value="" hidden>Pilih Lembaga</option>

                        <!-- Kelompok: Unit Formal -->
                        <optgroup label="Lembaga Formal">
                            @foreach ($lembaga['UnitFormal'] as $item)
                                <option value="{{ $item }}" {{ $petugas->lembaga == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </optgroup>

                        <!-- Kelompok: Asrama Pondok -->
                        <optgroup label="Asrama Pondok">
                            @foreach ($lembaga['AsramaPondok'] as $item)
                                <option value="{{ $item }}" {{ $petugas->lembaga == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </optgroup>

                        <!-- Kelompok: Tingkat Diniyah -->
                        <optgroup label="Tingkat Diniyah">
                            @foreach ($lembaga['TingkatDiniyah'] as $item)
                                <option value="{{ $item }}" {{ $petugas->lembaga == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>

                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        Role Petugas
                    </label>

                    <select id="role" name="role"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                        required>

                        <option value="" hidden>Pilih Role</option>

                        @php $selectedRole = $petugas->getRoleNames()->first(); @endphp

                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ $selectedRole == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>

                </div>




                <!-- Tombol -->
                <div class="pt-3 flex justify-end space-x-3">
                    <a href="{{ route('admin.petugas.index') }}"
                        class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow transition">
                        Perbarui Petugas
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
