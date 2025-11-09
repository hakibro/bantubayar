<table class="min-w-full border-collapse">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Nama</th>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Email</th>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Lembaga</th>
            <th class="px-4 py-3 text-center text-gray-600 text-sm font-semibold">Status</th>
            <th class="px-4 py-3 text-center text-gray-600 text-sm font-semibold">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($petugas as $item)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-800">{{ $item->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $item->email }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $item->lembaga ?? '-' }}</td>
                <td class="px-4 py-3 text-center">
                    @if ($item->deleted_at)
                        <span class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-full">Nonaktif</span>
                    @else
                        <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full">Aktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center space-x-2">

                    <a href="{{ route('admin.petugas.edit', $item->id) }}"
                        class="text-yellow-600 hover:underline">Edit</a>

                    @if (!$item->deleted_at)
                        <form action="{{ route('admin.petugas.destroy', $item->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Nonaktifkan petugas ini?')"
                                class="text-red-600 hover:underline">
                                Nonaktifkan
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.petugas.restore', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button class="text-green-600 hover:underline">Pulihkan</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada data petugas ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-6">
    {{ $petugas->links() }}
</div>
