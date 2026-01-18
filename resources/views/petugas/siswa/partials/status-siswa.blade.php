    <div class="whitespace-nowrap">
        <span
            class="{{ $item->getTotalTunggakan() < 0
                ? 'bg-accent'
                : ($item->getKategoriBelumLunas() === null
                    ? 'bg-yellow-600'
                    : 'bg-success') }} text-xs text-white px-3 py-1 rounded-full font-semibold">
            @if (is_null($item->getKategoriBelumLunas()))
                Belum Sinkron
            @elseif ($item->getTotalTunggakan() < 0)
                Belum Lunas
            @else
                Lunas
            @endif
        </span>

    </div>
