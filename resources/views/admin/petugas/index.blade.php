@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Petugas</h1>
                <p class="text-sm text-gray-500">Kelola data petugas aktif maupun nonaktif secara real-time.</p>
            </div>
            <a href="{{ route('admin.petugas.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Tambah Petugas
            </a>
        </div>

        <!-- Filter & Pencarian -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row gap-3 items-center bg-white p-4 rounded-lg shadow border border-gray-100">
                <!-- Input Pencarian -->
                <div class="w-full md:w-1/2">
                    <input type="text" id="searchInput" placeholder="Cari nama atau email..."
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Filter Lembaga -->
                <div class="w-full md:w-1/4">
                    <select id="filterLembaga"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Semua Lembaga</option>
                        @foreach ($daftarLembaga as $l)
                            <option value="{{ $l }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Container Tabel -->
        <div id="tableContainer" class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
            @include('admin.petugas.partials.table', ['petugas' => $petugas])
        </div>
    </div>

    <!-- Live Search Script -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const filterLembaga = document.getElementById('filterLembaga');
        const tableContainer = document.getElementById('tableContainer');

        function fetchPetugas() {
            const search = searchInput.value;
            const lembaga = filterLembaga.value;

            fetch(`{{ route('admin.petugas.index') }}?search=${encodeURIComponent(search)}&lembaga=${encodeURIComponent(lembaga)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // 🔥 penting
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                })
                .catch(err => console.error('Error:', err));
        }

        searchInput.addEventListener('keyup', fetchPetugas);
        filterLembaga.addEventListener('change', fetchPetugas);
    </script>
@endsection
