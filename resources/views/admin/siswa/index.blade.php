@extends('layouts.dashboard')

@section('title', 'Manage Siswa')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Manage Siswa</h1>
                <p class="text-sm text-gray-500">Sync Data Siswa dari Data Center - Informasi Pembayaran, Lembaga, Kelas.</p>
            </div>
            <div class="flex gap-3">
                <a href="javascript:void(0)" onclick="syncSiswa('{{ route('admin.siswa.sync-data-siswa') }}')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 flex items-center">
                    <i class="fas fa-database mr-2"></i> Sync Data Siswa
                </a>
                <a href="{{ route('admin.sync-pembayaran.index') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 flex items-center">
                    <i class="fas fa-money-bill-wave mr-2"></i> Sinkron Semua Pembayaran (Dev Only)
                </a>
            </div>

        </div>

        {{-- Search & Filters --}}
        <div class="mb-6">
            <div class="flex flex-col md:flex-row gap-3 items-center bg-white p-4 rounded-lg shadow border border-gray-100">
                <input id="searchInput" type="text" placeholder="Cari nama atau idperson..."
                    class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">

                <select id="filterLembaga" class="w-full md:w-1/4 px-4 py-2 border rounded-lg">
                    <option value="">Semua Lembaga</option>
                    @foreach ($daftarLembaga as $l)
                        <option value="{{ $l === '__NULL__' ? '__NULL__' : $l }}">
                            {{ $l === '__NULL__' ? 'Tanpa Lembaga' : $l }}
                        </option>
                    @endforeach
                    <option value="__NULL__">Tidak di Lembaga Formal</option>
                </select>

                <select id="filterKelas" class="w-full md:w-1/4 px-4 py-2 border rounded-lg">
                    <option value="">Semua Kelas</option>
                </select>

                <select id="filterAsrama" class="w-full md:w-1/4 px-4 py-2 border rounded-lg">
                    <option value="">Semua Asrama</option>
                    @foreach ($daftarAsrama as $l)
                        <option value="{{ $l === '__NULL__' ? '__NULL__' : $l }}">
                            {{ $l === '__NULL__' ? 'Tanpa Lembaga' : $l }}
                        </option>
                    @endforeach
                </select>

                <select id="filterKamar" class="w-full md:w-1/4 px-4 py-2 border rounded-lg">
                    <option value="">Semua Kamar</option>
                </select>



                <select id="filterPetugas" class="w-full md:w-1/4 px-4 py-2 border rounded-lg">
                    <option value="">Semua Petugas</option>
                    @foreach ($petugas as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
            <div id="tableContainer">
                @include('admin.siswa.partials.table', ['siswa' => $siswa, 'petugas' => $petugas])
            </div>
        </div>
    </div>


    <!-- Modal Notifikasi -->
    <div id="notifModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
            <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold mb-4" id="notifTitle">Notifikasi</h3>
            <p class="text-gray-700" id="notifMessage"></p>
            <ul class="mt-4 text-sm text-gray-600" id="notifDetails"></ul>
            <div class="mt-6 text-right">
                <button id="okModal" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">OK</button>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
            <p class="mt-3 text-gray-700 font-semibold">Memproses... Mohon tunggu</p>
        </div>
    </div>

    @push('scripts')
        <script>
            function syncSiswa(url) {

                // Tampilkan loading
                $('#loadingModal').removeClass('hidden');

                $.ajax({
                    url: url,
                    method: "GET",

                    success: function(data) {

                        // Tutup loading
                        $('#loadingModal').addClass('hidden');

                        // Set judul
                        $('#notifTitle').text(data.status ? 'Sukses' : 'Gagal');
                        $('#notifMessage').text(data.message);

                        // Detail
                        let details =
                            `<li>Inserted: ${data.inserted}</li>
        <li>Updated: ${data.updated}</li>
        <li>Deleted: ${data.deleted}</li>
        <li>Skipped: ${data.skipped}</li>
        <li>Total API: ${data.total_api}</li>
        <li>Total Local: ${data.total_local}</li>`;

                        $('#notifDetails').html(details);

                        // Tampilkan modal notif
                        $('#notifModal').removeClass('hidden');
                    },

                    error: function() {
                        // Tutup loading
                        $('#loadingModal').addClass('hidden');

                        $('#notifTitle').text('Error');
                        $('#notifMessage').text('Terjadi kesalahan saat sinkronisasi.');
                        $('#notifDetails').html('');
                        $('#notifModal').removeClass('hidden');
                    }
                });
            }

            // Tutup modal notifikasi
            $('#closeModal, #okModal').click(function() {
                $('#notifModal').addClass('hidden');
            });
        </script>
    @endpush

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ===== ELEMENTS =====
            const searchInput = document.getElementById('searchInput');
            const filterLembaga = document.getElementById('filterLembaga');
            const filterKelas = document.getElementById('filterKelas');
            const filterAsrama = document.getElementById('filterAsrama');
            const filterKamar = document.getElementById('filterKamar');
            const filterPetugas = document.getElementById('filterPetugas');
            const tableContainer = document.getElementById('tableContainer');


            // ===== UTILITY FUNCTIONS =====
            function debounce(fn, ms) {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), ms);
                };
            }

            // ===== FETCH TABLE =====
            function fetchSiswa() {
                const params = new URLSearchParams({
                    search: searchInput.value,
                    lembaga: filterLembaga.value,
                    kelas: filterKelas.value,
                    asrama: filterAsrama.value,
                    kamar: filterKamar.value,
                    petugas_id: filterPetugas.value
                });

                fetch(`{{ route('admin.siswa.index') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                    })
                    .catch(err => {
                        console.error('Error loading table:', err);
                    });
            }

            // ===== FILTER EVENTS =====
            filterLembaga.addEventListener('change', () => {
                fetch(`{{ route('admin.siswa.kelas') }}?lembaga=${filterLembaga.value}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(kelas => {
                        filterKelas.innerHTML = `<option value="">Semua Kelas</option>`;
                        kelas.forEach(k => {
                            filterKelas.innerHTML += `<option value="${k}">${k}</option>`;
                        });
                        fetchSiswa();
                    });
            });

            filterAsrama.addEventListener('change', () => {
                fetch(`{{ route('admin.siswa.kamar') }}?asrama=${filterAsrama.value}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(kamar => {
                        filterKamar.innerHTML = `<option value="">Semua Kamar</option>`;
                        kamar.forEach(k => {
                            filterKamar.innerHTML += `<option value="${k}">${k}</option>`;
                        });
                        fetchSiswa();
                    });
            });

            searchInput.addEventListener('keyup', debounce(fetchSiswa, 300));
            filterKelas.addEventListener('change', fetchSiswa);
            filterKamar.addEventListener('change', fetchSiswa);
            filterPetugas.addEventListener('change', fetchSiswa);

        });
    </script>
@endsection
