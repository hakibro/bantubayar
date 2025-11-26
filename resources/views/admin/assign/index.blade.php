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
            <div class="flex flex-col md:flex-row gap-3 items-center bg-white p-4 rounded-xl shadow border border-gray-100">

                <!-- Input Search -->
                <input id="searchInput" type="text" placeholder="Cari nama atau idperson..."
                    class="w-full md:w-1/3 px-4 py-2.5 border border-gray-300 rounded-xl
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   transition-all duration-150 text-gray-700 placeholder:text-gray-400">

                <!-- Filter Lembaga -->
                <div class="relative w-full md:w-1/4">
                    <select id="filterLembaga"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-gray-700
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                       transition-all duration-150 cursor-pointer">

                        <option value="" class="text-gray-500 font-medium">Semua Lembaga</option>

                        @foreach ($daftarLembaga as $l)
                            <option value="{{ $l === '__NULL__' ? '__NULL__' : $l }}">
                                {{ $l === '__NULL__' ? 'Tanpa Lembaga' : $l }}
                            </option>
                        @endforeach

                        <option value="__NULL__" class="text-gray-600 font-medium">Tidak di Lembaga Formal</option>
                    </select>
                </div>

                <!-- Filter Kelas -->
                <div class="relative w-full md:w-1/4">
                    <select id="filterKelas"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-gray-700
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                       transition-all duration-150 cursor-pointer">

                        <option value="" class="text-gray-500 font-medium">Semua Kelas</option>
                    </select>
                </div>

                <!-- Filter Asrama -->
                <div class="relative w-full md:w-1/4">
                    <select id="filterAsrama"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-gray-700
               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
               transition-all duration-150 cursor-pointer">

                        <option value="" class="text-gray-500 font-medium">Semua Asrama</option>
                        @foreach ($daftarAsrama as $l)
                            <option value="{{ $l === '__NULL__' ? '__NULL__' : $l }}">
                                {{ $l === '__NULL__' ? 'Tanpa Asrama' : $l }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kamar -->
                <div class="relative w-full md:w-1/4">
                    <select id="filterKamar"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-gray-700
               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
               transition-all duration-150 cursor-pointer">

                        <option value="" class="text-gray-500 font-medium">Semua Kamar</option>
                    </select>
                </div>




                <!-- Filter Petugas -->
                <div class="relative w-full md:w-1/4">
                    <select id="filterPetugas"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-gray-700
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                       transition-all duration-150 cursor-pointer">

                        <option value="" class="text-gray-500 font-medium">Semua Petugas</option>

                        @foreach ($petugas as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>




        <!-- TABLE -->
        <div class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
            <div id="tableContainer">
                @include('admin.assign.partials.table', ['siswa' => $siswa, 'petugas' => $petugas])
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-80 p-6 rounded shadow-lg">
            <h2 class="text-lg font-semibold mb-4">Assign Petugas</h2>
            <form id="assignForm">@csrf
                <div id="assignIdsContainer"></div>
                <select name="petugas_id" id="assignPetugasSelect" class="w-full mb-4 border px-2 py-1 rounded">
                    <option value="">— pilih petugas —</option>
                    @foreach ($petugas as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <div class="flex justify-end gap-2">
                    <button type="button" class="closeModal px-3 py-1 bg-gray-300 rounded">Batal</button>
                    <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <div id="bulkAssignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-80 p-6 rounded shadow-lg">
            <h2 class="text-lg font-semibold mb-4">Assign Petugas (Bulk)</h2>
            <form id="bulkAssignForm">@csrf
                <div id="bulkAssignIdsContainer"></div>
                <select name="petugas_id" id="bulkPetugasSelect" class="w-full mb-4 border px-2 py-1 rounded">
                    <option value="">— pilih petugas —</option>
                    @foreach ($petugas as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <div class="flex justify-end gap-2">
                    <button type="button" class="closeModal px-3 py-1 bg-gray-300 rounded">Batal</button>
                    <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <div id="confirmUnassignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-80 p-6 rounded shadow-lg">
            <h2 class="text-lg font-semibold mb-4 text-red-600">Konfirmasi Unassign</h2>
            <p class="mb-4">Hapus petugas dari siswa terpilih?</p>
            <form id="unassignForm">@csrf
                <div id="unassignIdsContainer"></div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="closeModal px-3 py-1 bg-gray-300 rounded">Batal</button>
                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

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

            const assignModal = document.getElementById("assignModal");
            const bulkAssignModal = document.getElementById("bulkAssignModal");
            const confirmUnassignModal = document.getElementById("confirmUnassignModal");

            // ===== UTILITY FUNCTIONS =====
            function debounce(fn, ms) {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), ms);
                };
            }

            function refreshBulkActionVisibility() {
                const bulkActionBar = document.getElementById('bulkActionBar');
                const anyChecked = document.querySelectorAll('.checkItem:checked').length > 0;
                if (bulkActionBar) {
                    bulkActionBar.classList.toggle('hidden', !anyChecked);
                }
            }

            function getCheckedIds() {
                return [...document.querySelectorAll('.checkItem:checked')].map(cb => cb.value);
            }

            function fillContainer(containerId, ids) {
                const container = document.getElementById(containerId);
                container.innerHTML = '';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'siswa_ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
            }

            function closeAllModals() {
                [assignModal, bulkAssignModal, confirmUnassignModal].forEach(modal => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
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

                fetch(`{{ route('admin.assign.index') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                        const checkAllTop = document.getElementById("checkAllTop");
                        if (checkAllTop) checkAllTop.checked = false;
                        refreshBulkActionVisibility();
                    })
                    .catch(err => {
                        console.error('Error loading table:', err);
                    });
            }

            // ===== FILTER EVENTS =====
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
            filterAsrama.addEventListener('change', () => {
                fetch(`{{ route('admin.assign.kamar') }}?asrama=${filterAsrama.value}`, {
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

            // ===== EVENT DELEGATION =====
            document.addEventListener("click", function(e) {
                // Close modal buttons
                if (e.target.classList.contains("closeModal")) {
                    closeAllModals();
                }

                // Single Assign
                if (e.target.classList.contains("singleAssignBtn")) {
                    const id = e.target.dataset.id;
                    fillContainer("assignIdsContainer", [id]);
                    assignModal.classList.remove("hidden");
                    assignModal.classList.add("flex");
                }

                // Bulk Assign
                if (e.target.id === "bulkAssignBtn") {
                    const ids = getCheckedIds();
                    if (!ids.length) return alert("Pilih siswa terlebih dahulu");
                    fillContainer("bulkAssignIdsContainer", ids);
                    bulkAssignModal.classList.remove("hidden");
                    bulkAssignModal.classList.add("flex");
                }

                // Bulk Unassign
                if (e.target.id === "bulkUnassignBtn") {
                    const ids = getCheckedIds();
                    if (!ids.length) return alert("Pilih siswa terlebih dahulu");
                    fillContainer("unassignIdsContainer", ids);
                    confirmUnassignModal.classList.remove("hidden");
                    confirmUnassignModal.classList.add("flex");
                }

                // Pagination
                if (e.target.classList.contains("ajaxPage")) {
                    e.preventDefault();
                    fetch(e.target.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            tableContainer.innerHTML = html;
                            const checkAllTop = document.getElementById("checkAllTop");
                            if (checkAllTop) checkAllTop.checked = false;
                            refreshBulkActionVisibility();
                        });
                }
            });

            // ===== CHECKBOX EVENTS =====
            document.addEventListener("change", function(e) {
                if (e.target.id === "checkAllTop") {
                    const checked = e.target.checked;
                    document.querySelectorAll(".checkItem").forEach(cb => cb.checked = checked);
                    refreshBulkActionVisibility();
                }

                if (e.target.classList.contains("checkItem")) {
                    const checkAllTop = document.getElementById("checkAllTop");
                    if (checkAllTop) {
                        const all = document.querySelectorAll(".checkItem");
                        const checked = document.querySelectorAll(".checkItem:checked");
                        checkAllTop.checked = checked.length === all.length;
                    }
                    refreshBulkActionVisibility();
                }
            });

            // ===== FORM SUBMITS =====
            document.getElementById("assignForm").addEventListener("submit", async function(e) {
                e.preventDefault();
                const petugas = document.getElementById("assignPetugasSelect").value;
                const siswa_ids = [...document.querySelectorAll("#assignIdsContainer input")].map(x => x
                    .value);

                if (!petugas || !siswa_ids.length) return alert("Pilih petugas dan siswa");

                try {
                    const res = await fetch("{{ route('admin.assign.bulk') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": this.querySelector("input[name=_token]").value,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            siswa_ids,
                            petugas_id: petugas
                        })
                    });
                    const json = await res.json();
                    alert(json.message);
                    closeAllModals();
                    fetchSiswa();
                } catch (err) {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan');
                }
            });

            document.getElementById("bulkAssignForm").addEventListener("submit", async function(e) {
                e.preventDefault();
                const petugas = document.getElementById("bulkPetugasSelect").value;
                const siswa_ids = [...document.querySelectorAll("#bulkAssignIdsContainer input")].map(
                    x => x.value);

                if (!petugas || !siswa_ids.length) return alert("Pilih petugas dan siswa");

                try {
                    const res = await fetch("{{ route('admin.assign.bulk') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": this.querySelector("input[name=_token]").value,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            siswa_ids,
                            petugas_id: petugas
                        })
                    });
                    const json = await res.json();
                    alert(json.message);
                    closeAllModals();
                    fetchSiswa();
                } catch (err) {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan');
                }
            });

            document.getElementById("unassignForm").addEventListener("submit", async function(e) {
                e.preventDefault();
                const siswa_ids = [...document.querySelectorAll("#unassignIdsContainer input")].map(x =>
                    x.value);

                if (!siswa_ids.length) return alert("Tidak ada siswa terpilih");

                try {
                    const res = await fetch("{{ route('admin.assign.bulkUnassign') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": this.querySelector('input[name=_token]').value,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            siswa_ids
                        })
                    });
                    const json = await res.json();
                    alert(json.message);
                    closeAllModals();
                    fetchSiswa();
                } catch (err) {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan');
                }
            });
        });
    </script>
@endsection
