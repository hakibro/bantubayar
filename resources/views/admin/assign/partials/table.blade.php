<div id="tableContainer">
    <table class="min-w-full border-collapse">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3">
                    <input type="checkbox" id="checkAllTop" class="w-4 h-4">
                </th>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Lembaga Formal</th>
                <th class="px-4 py-3 text-left">Petugas Saat Ini</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr id="bulkActionBar" class="bg-gray-50 hidden">
                <td colspan="5" class="px-4 py-2">
                    <div class="flex items-center gap-3 mb-3">
                        <button id="bulkAssignBtn" class="px-3 py-1 bg-blue-600 text-white rounded">Assign</button>
                        <button id="bulkUnassignBtn" class="px-3 py-1 bg-red-600 text-white rounded">Unassign</button>
                    </div>
                </td>
            </tr>

            @forelse ($siswa as $item)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" class="checkItem w-4 h-4" value="{{ $item->id }}">
                    </td>
                    <td class="px-4 py-3">{{ $item->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->UnitFormal ?? 'Tidak ada' }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        @php $assigned = $item->petugas->first(); @endphp
                        {{ $assigned ? $assigned->name : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" class="px-3 py-1 bg-blue-600 text-white rounded singleAssignBtn"
                            data-id="{{ $item->id }}">Assign</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada siswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-4 py-2">
        @include('admin.assign.partials.pagination', ['paginator' => $siswa])
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
                <button type="button" onclick="assignModal.classList.add('hidden');"
                    class="px-3 py-1 bg-gray-300 rounded">Batal</button>
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
                <button type="button" onclick="bulkAssignModal.classList.add('hidden');"
                    class="px-3 py-1 bg-gray-300 rounded">Batal</button>
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
                <button type="button" onclick="confirmUnassignModal.classList.add('hidden');"
                    class="px-3 py-1 bg-gray-300 rounded">Batal</button>
                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const assignModal = document.getElementById("assignModal");
        const bulkAssignModal = document.getElementById("bulkAssignModal");
        const confirmUnassignModal = document.getElementById("confirmUnassignModal");

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

        // ===== Event delegation pada document untuk handle dynamic content =====
        document.addEventListener("click", function(e) {
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
        });

        // ===== Checkbox events dengan delegation =====
        document.addEventListener("change", function(e) {
            // Check All
            if (e.target.id === "checkAllTop") {
                const checked = e.target.checked;
                document.querySelectorAll(".checkItem").forEach(cb => cb.checked = checked);
                refreshBulkActionVisibility();
            }

            // Individual checkbox
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

        // ===== Form submits (ini tidak perlu delegation karena modal tidak di-reload) =====
        document.getElementById("assignForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const petugas = document.getElementById("assignPetugasSelect").value;
            const siswa_ids = [...document.querySelectorAll("#assignIdsContainer input")].map(x => x
                .value);

            if (!petugas || !siswa_ids.length) return alert("Pilih petugas dan siswa");

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
            assignModal.classList.add("hidden");
            assignModal.classList.remove("flex");
            reloadTable();
        });

        document.getElementById("bulkAssignForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const petugas = document.getElementById("bulkPetugasSelect").value;
            const siswa_ids = [...document.querySelectorAll("#bulkAssignIdsContainer input")].map(
                x => x.value);

            if (!petugas || !siswa_ids.length) return alert("Pilih petugas dan siswa");

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
            bulkAssignModal.classList.add("hidden");
            bulkAssignModal.classList.remove("flex");
            reloadTable();
        });

        document.getElementById("unassignForm").addEventListener("submit", async function(e) {
            e.preventDefault();
            const siswa_ids = [...document.querySelectorAll("#unassignIdsContainer input")].map(x =>
                x.value);

            if (!siswa_ids.length) return alert("Tidak ada siswa terpilih");

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
            confirmUnassignModal.classList.add("hidden");
            confirmUnassignModal.classList.remove("flex");
            reloadTable();
        });

        // ===== Reload Table =====
        function reloadTable() {
            fetch("{{ route('admin.assign.index') }}", {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById("tableContainer").innerHTML = html;
                    // Reset checkbox state setelah reload
                    const checkAllTop = document.getElementById("checkAllTop");
                    if (checkAllTop) checkAllTop.checked = false;
                    refreshBulkActionVisibility();
                })
                .catch(err => {
                    console.error('Error reloading table:', err);
                    alert('Gagal memuat ulang tabel');
                });
        }
    });
</script>
