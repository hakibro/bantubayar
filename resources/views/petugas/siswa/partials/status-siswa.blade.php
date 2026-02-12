<div class="whitespace-nowrap mt-2 flex items-center">
    <span class="border-2 {{ $item->status_pembayaran_badge }} text-xs px-3 py-1 rounded-full font-semibold">
        {{ $item->status_pembayaran_label }}
    </span>

    <span class="ml-2">
        @if ($item->sedangDitangani())
            <span class="text-xs font-semibold text-yellow-600 truncate italic">
                Sedang ditangani {{ $item->petugasPenangananAktif() }}
            </span>
        @elseif ($item->is_lunas && $item->penangananLunas()?->updated_at->isSameMonth(now()))
            <span class="text-xs font-semibold text-green-600 italic">
                Telah ditangani {{ $item->penangananLunas()->petugas->name }}
            </span>
        @endif
    </span>
</div>
