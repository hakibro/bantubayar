@forelse ($alumni as $item)
    <div id="alumni-{{ $item->idperson }}"
        class="group bg-white rounded-2xl border border-gray-100 hover:shadow-lg transition-all p-4">

        <div class="flex justify-between items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-base leading-tight truncate">
                        {{ $item->nama }}
                    </h3>
                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-wider bg-gray-800 text-white px-2 py-0.5 rounded-full">
                        Alumni
                    </span>
                </div>

                <div
                    class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">
                    <span class="text-xs font-mono font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        {{ $item->idperson }}
                    </span>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-graduation-cap text-blue-400"></i>
                        <span>{{ $item->lembaga ?? '-' }} • {{ $item->kelas_lulus ?? '-' }}</span>
                    </div>
                    @if ($item->tahun_lulus)
                        <div class="flex items-center gap-1">
                            <i class="fas fa-calendar-check text-purple-400"></i>
                            <span>Lulus {{ $item->tahun_lulus }}</span>
                        </div>
                    @endif
                    @if ($item->phone)
                        <div class="flex items-center gap-1">
                            <i class="fas fa-phone text-green-400"></i>
                            <span>{{ $item->phone }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <span class="text-xs font-semibold {{ ($item->total_tunggakan ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Tunggakan: Rp {{ number_format((int) ($item->total_tunggakan ?? 0), 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-gray-500">
                        Saldo: Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-2 shrink-0">
                <a href="{{ route('alumni.show', $item->idperson) }}"
                    class="p-2.5 md:px-4 md:py-1.5 bg-blue-600 text-white rounded-xl hover:bg-black transition flex items-center justify-center shadow-sm"
                    title="Aksi">
                    <i class="fas fa-arrow-right text-sm"></i>
                    <span class="inline ml-2 text-[11px] font-bold uppercase">Aksi</span>
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="col-span-full py-12 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
        <p class="text-gray-500 font-medium">Data alumni tidak ditemukan.</p>
    </div>
@endforelse
