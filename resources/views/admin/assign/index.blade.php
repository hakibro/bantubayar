@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Assign Siswa ke Petugas</h1>
                <p class="text-sm text-gray-500">Assign siswa ke petugas — gunakan filter untuk mencari.</p>
            </div>
        </div>

        <!-- Filter bar -->
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
                </select>

                <select id="filterKelas" class="w-full md:w-1/4 px-4 py-2 border rounded-lg">
                    <option value="">Semua Kelas</option>
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
        <div id="tableContainer" class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
            @include('admin.assign.partials.table', ['siswa' => $siswa, 'petugas' => $petugas])
        </div>
    </div>

    <!-- Modal Assign Petugas -->
    <div id="modalAssign" class="fixed inset-0 bg-black/40 hidden items-center justify-center">
        <div class="bg-white p-6 rounded shadow w-80">
            <h3 class="text-lg font-semibold mb-3">Pilih Petugas</h3>

            <select id="bulkAssignPetugas" class="w-full border px-3 py-2 rounded mb-4">
                <option value="">-- Pilih Petugas --</option>
                @foreach ($petugas as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <div class="flex justify-end gap-2">
                <button onclick="closeAssignModal()" class="px-3 py-1 bg-gray-300 rounded">Batal</button>
                <button id="confirmBulkAssign" class="px-3 py-1 bg-blue-600 text-white rounded">Assign</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Unassign -->
    <div id="modalUnassign" class="fixed inset-0 bg-black/40 hidden items-center justify-center">
        <div class="bg-white p-6 rounded shadow w-80">
            <h3 class="text-lg font-semibold mb-3">Yakin ingin Unassign?</h3>
            <p class="text-sm text-gray-600 mb-4">Semua siswa terpilih akan dihapus petugasnya.</p>

            <div class="flex justify-end gap-2">
                <button onclick="closeUnassignModal()" class="px-3 py-1 bg-gray-300 rounded">Batal</button>
                <button id="confirmBulkUnassign" class="px-3 py-1 bg-red-600 text-white rounded">Unassign</button>
            </div>
        </div>
    </div>


    <script>
        /* ============================================================
                               FETCH TABLE
                            ============================================================ */
        function fetchSiswa() {
            const params = new URLSearchParams({
                search: searchInput.value,
                lembaga: filterLembaga.value,
                kelas: filterKelas.value,
                petugas_id: filterPetugas.value
            });

            fetch(`{{ route('admin.assign.index') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                });
        }

        /* ============================================================
           FILTER ELEMENTS
        ============================================================ */
        const searchInput = document.getElementById('searchInput');
        const filterLembaga = document.getElementById('filterLembaga');
        const filterKelas = document.getElementById('filterKelas');
        const filterPetugas = document.getElementById('filterPetugas');
        const tableContainer = document.getElementById('tableContainer');

        /* Lembaga → load kelas */
        filterLembaga.addEventListener('change', () => {
            fetch(`{{ route('admin.assign.kelas') }}?lembaga=${filterLembaga.value}`, {
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

        searchInput.addEventListener('keyup', debounce(fetchSiswa, 300));
        filterKelas.addEventListener('change', fetchSiswa);
        filterPetugas.addEventListener('change', fetchSiswa);

        function debounce(fn, ms) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), ms);
            };
        }



        /* ============================================================
           PAGINATION (AJAX)
        ============================================================ */
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("ajaxPage")) {
                e.preventDefault();
                fetch(e.target.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => tableContainer.innerHTML = html);
            }
        });
    </script>
@endsection
