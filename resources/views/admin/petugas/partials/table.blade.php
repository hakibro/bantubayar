<table class="min-w-full border-collapse">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Nama</th>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Email</th>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Lembaga</th>
            <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Role</th>
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
                <td class="px-4 py-3 text-gray-600">
                    @if ($item->hasRole('petugas'))
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                            Petugas
                        </span>
                    @endif

                    @if ($item->hasRole('bendahara'))
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                            Bendahara
                        </span>
                    @endif

                </td>

                <td class="px-4 py-3 text-center">
                    @if ($item->deleted_at)
                        <span class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-full">Nonaktif</span>
                    @else
                        <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full">Aktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center space-x-2">

                    {{-- Tombol Edit --}}
                    <a href="{{ route('admin.petugas.edit', $item->id) }}"
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded hover:bg-yellow-200 transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>

                    @if (!$item->deleted_at)
                        {{-- Tombol Nonaktifkan --}}
                        <form action="{{ route('admin.petugas.destroy', $item->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Nonaktifkan petugas ini?')"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded hover:bg-red-200 transition">
                                <i class="fas fa-user-slash mr-1"></i> Nonaktifkan
                            </button>
                        </form>
                    @else
                        {{-- Tombol Pulihkan --}}
                        <form action="{{ route('admin.petugas.restore', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded hover:bg-green-200 transition">
                                <i class="fas fa-undo mr-1"></i> Pulihkan
                            </button>
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
