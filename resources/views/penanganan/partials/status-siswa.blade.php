<div class="whitespace-nowrap mt-2 flex items-center">
    <span class="border-2 {{ $item->siswa->status_pembayaran_badge }} text-xs px-3 py-1 rounded-full font-semibold">
        {{ $item->siswa->status_pembayaran_label }}
    </span>

    <span class="ml-2">
        <span class="text-xs font-semibold text-yellow-600 truncate italic px-3 py-1 rounded-full border-2">
            {{ $item->status }}
        </span>

    </span>
</div>
