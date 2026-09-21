@foreach ($homeVisits as $item)
    @php
        $badge = match ($item->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'disetujui' => 'bg-blue-100 text-blue-800',
            'dilaksanakan' => 'bg-indigo-100 text-indigo-800',
            'selesai' => 'bg-green-100 text-green-800',
            'ditolak' => 'bg-red-100 text-red-800',
            'batal' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    @endphp
    <tr>
        <td class="px-6 py-4 whitespace-nowrap">
            <p class="text-sm font-medium">{{ $item->siswa->nama ?? '-' }}</p>
            <p class="text-xs text-gray-500">{{ $item->siswa->idperson ?? '-' }}</p>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->pengaju->name ?? '-' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            @if ($item->petugas_nama)
                {{ $item->petugas_nama }}
                <span class="text-xs text-gray-400">({{ $item->petugas_hp ?? '-' }})</span>
            @else
                <span class="text-gray-400 italic">share link</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            {{ $item->created_at?->format('d/m/Y H:i') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 text-xs rounded-full {{ $badge }}">{{ ucfirst($item->status) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            <a href="{{ route('admin.home-visit.show', $item->id) }}"
                class="text-primary hover:underline font-medium">Detail</a>
            @if ($item->status === 'pending')
                <form action="{{ route('admin.home-visit.approve', $item->id) }}" method="POST"
                    class="inline">
                    @csrf
                    <button type="submit" class="text-green-600 hover:underline ml-2">Setujui</button>
                </form>
            @endif
        </td>
    </tr>
@endforeach
