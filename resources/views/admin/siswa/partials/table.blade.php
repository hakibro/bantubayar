<!-- admin/assign/partials/table.blade.php -->
<table class="min-w-full border-collapse">
    <thead class="bg-gray-100">
        <tr>

            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Lembaga Formal</th>
            <th class="px-4 py-3 text-left">Pondok</th>
            <th class="px-4 py-3 text-left">Petugas Saat Ini</th>
            <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>


        @forelse ($siswa as $item)
            <tr class="border-b hover:bg-gray-50">

                <td class="px-4 py-3">{{ $item->nama }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $item->UnitFormal ?? 'Tidak ada' }} -
                    {{ $item->KelasFormal ?? 'Tidak ada' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $item->AsramaPondok ?? 'Tidak ada' }} -
                    {{ $item->KamarPondok ?? 'Tidak ada' }}</td>
                <td class="px-4 py-3 text-gray-600">
                    @php $assigned = $item->petugas->first(); @endphp
                    {{ $assigned ? $assigned->name : '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('admin.siswa.show', $item->id) }}"
                        class="px-3 py-1 bg-blue-600 text-white rounded inline-block">
                        Detail
                    </a>
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
    @include('admin.siswa.partials.pagination', ['paginator' => $siswa])
</div>
