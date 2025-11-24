<!-- admin/assign/partials/table.blade.php -->
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
