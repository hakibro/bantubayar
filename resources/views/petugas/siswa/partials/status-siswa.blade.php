    <div class="whitespace-nowrap mt-2 flex items-center">
        <span
            class="border-2 {{ $item->getTotalTunggakan() < 0
                ? ' border-red-400 text-red-600'
                : ($item->getKategoriBelumLunas() === null
                    ? 'border-yellow-400 text-yellow-600'
                    : 'border-green-400 text-green-600') }} text-xs px-3 py-1 rounded-full font-semibold">
            @if (is_null($item->getKategoriBelumLunas()))
                Belum Sinkron
            @elseif ($item->getTotalTunggakan() < 0)
                Belum Lunas
            @else
                Lunas
            @endif
        </span>
        <span class="ml-2">
            @if ($item->sedangDitangani())
                <span class="text-xs font-semibold text-yellow-600 truncate italic">
                    Sedang ditangani
                    {{ $item->petugasPenangananAktif() }}
                </span>
            @elseif (
                $item->penangananLunas() &&
                    $item->getTotalTunggakan() == 0 &&
                    $item->penangananLunas()->updated_at->isSameMonth(now()))
                <span class="text-xs font-semibold text-green-600 text-sm italic">
                    Telah ditangani
                    {{ $item->penangananLunas()->petugas->name }}
                </span>
            @else
                <span class="text-xs font-semibold text-gray-500 text-sm italic">
                    Rp {{ number_format($item->getTotalTunggakan(), 0, ',', '.') }}
                </span>
            @endif
        </span>


    </div>
