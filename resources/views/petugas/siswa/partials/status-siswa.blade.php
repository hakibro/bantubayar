    <div class="whitespace-nowrap">
        @if (is_null($item->getKategoriBelumLunas()))
            <span class="text-xs bg-yellow-600 text-white px-3 py-1 rounded-full font-semibold">Belum
                Sinkron</span>
        @elseif ($belumLunas < 0)
            <span class="text-xs bg-red-600 text-white px-3 py-1 rounded-full font-semibold">Belum
                Lunas</span>
        @else
            <span class="text-xs bg-green-600 text-white px-3 py-1 rounded-full font-semibold">Lunas</span>
        @endif
    </div>
