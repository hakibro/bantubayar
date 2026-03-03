@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h1 class="text-2xl font-bold mb-6">Buat Home Visit</h1>

                <!-- Data Siswa -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h2 class="font-semibold text-lg mb-2">Data Siswa</h2>
                    <p><span class="text-gray-600">Nama:</span> {{ $siswa->nama }}</p>
                    <p><span class="text-gray-600">ID Person:</span> {{ $siswa->idperson }}</p>
                    <p><span class="text-gray-600">Lembaga:</span> {{ $siswa->UnitFormal ?? '-' }}</p>
                    <p><span class="text-gray-600">Kelas:</span> {{ $siswa->KelasFormal ?? '-' }}</p>
                    <p><span class="text-gray-600">Asrama:</span> {{ $siswa->AsramaPondok ?? '-' }}</p>
                    <p><span class="text-gray-600">Kamar:</span> {{ $siswa->KamarPondok ?? '-' }}</p>
                    <p><span class="text-gray-600">No. HP:</span> {{ $siswa->phone ?? '-' }}</p>
                    <p><span class="text-gray-600">Total Tunggakan:</span> Rp
                        {{ number_format($siswa->getTotalTunggakan(), 0, ',', '.') }}</p>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.home-visit.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Petugas Visit</label>
                        <input type="text" name="petugas_nama" required
                            class="w-full border rounded-lg px-3 py-2 @error('petugas_nama') border-red-500 @enderror">
                        @error('petugas_nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Petugas</label>
                        <input type="text" name="petugas_hp" required
                            class="w-full border rounded-lg px-3 py-2 @error('petugas_hp') border-red-500 @enderror">
                        @error('petugas_hp')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Visit (Opsional)</label>
                        <input type="date" name="tanggal_visit"
                            class="w-full border rounded-lg px-3 py-2 @error('tanggal_visit') border-red-500 @enderror">
                        @error('tanggal_visit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.home-visit.select') }}"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</a>
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Simpan & Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
