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
                <button id="startSync" class="px-4 py-2 bg-blue-600 text-white">Mulai Sinkron Pembayaran</button>

            </div>
        </div>
        <!-- Progressbar  -->
        <div id="progressContainer" class="hidden w-full my-4 bg-gray-200 rounded h-6">
            <div id="progressBar" class="bg-green-500 h-6 rounded text-center text-white text-sm" style="width:0%">
                0%
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
                    <option value="__NULL__">Tidak di Lembaga Formal</option>
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
        <div class="bg-white rounded-lg shadow border border-gray-100 overflow-hidden">
            <div id="tableContainer">
                @include('admin.assign.partials.table', ['siswa' => $siswa, 'petugas' => $petugas])
            </div>
        </div>
    </div>



    <script>
        document.getElementById('startSync').addEventListener('click', function() {

            // Mulai sync
            fetch('/admin/siswa/sync-pembayaran-siswa')
                .then(response => response.json());

            //show progress bar
            document.getElementById('progressContainer').classList.remove('hidden');

            // Polling progress tiap 500ms
            let interval = setInterval(() => {

                fetch('/admin/siswa/get-progress-pembayaran')
                    .then(res => res.json())
                    .then(data => {
                        let p = data.progress || 0;

                        let bar = document.getElementById('progressBar');
                        bar.style.width = p + "%";
                        bar.innerHTML = p + "%";

                        if (p >= 100) {
                            clearInterval(interval);
                        }
                    });

            }, 500);
        });
        document.addEventListener("DOMContentLoaded", function() {
            // ===== ELEMENTS =====
            const searchInput = document.getElementById('searchInput');
            const filterLembaga = document.getElementById('filterLembaga');
            const filterKelas = document.getElementById('filterKelas');
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

            searchInput.addEventListener('keyup', debounce(fetchSiswa, 300));
            filterKelas.addEventListener('change', fetchSiswa);
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
