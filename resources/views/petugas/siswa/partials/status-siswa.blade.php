<div class="whitespace-nowrap mt-2 flex items-center">
    <span class="border-2 {{ $item->status_pembayaran_badge }} text-xs px-3 py-1 rounded-full font-semibold">
        {{ $item->status_pembayaran_label }}
    </span>

    <span class="ml-2">
        @php
            $penangananAktif = $item->penangananAktif();
            $penangananLunasItem = $item->penangananLunas();
        @endphp

        @if ($penangananAktif)
            <span class="text-xs font-semibold text-yellow-600 truncate italic">
                Sedang ditangani {{ $penangananAktif?->petugas?->name ?? 'Petugas' }}
            </span>
        @elseif ($item->statusLunas?->is_lunas && $penangananLunasItem)
            <span class="text-xs font-semibold text-green-600 italic">
                Telah ditangani {{ $penangananLunasItem->petugas?->name ?? 'Petugas' }}
                ({{ $penangananLunasItem->updated_at?->translatedFormat('d M Y') ?? 'Tidak Diketahui' }})
            </span>
        @endif
    </span>
</div>
