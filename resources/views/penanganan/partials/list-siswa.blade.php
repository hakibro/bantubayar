@forelse ($listPenanganan as $item)
    <div id="siswa-{{ $item->siswa->id }}"
        class="group {{ $item->siswa->is_lunas === true && $item->siswa->penangananLunas()?->updated_at->isSameMonth(now())
            ? 'bg-gray-100 border-none shadow-none text-gray-400'
            : 'bg-white' }} rounded-2xl border border-gray-100 hover:shadow-lg transition-all p-4">

        <div class="flex justify-between items-center gap-3">
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-base leading-tight truncate">
                    {{ $item->siswa->nama }}
                </h3>
                <div class="flex items-center gap-2">


                </div>

                <div
                    class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">

                    <span class="text-xs font-mono font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        {{ $item->siswa->idperson }}
                    </span>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-graduation-cap text-blue-400"></i>
                        <span>{{ $item->siswa->UnitFormal ?? '-' }} - {{ $item->siswa->KelasFormal ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-home text-green-400"></i>
                        <span>{{ $item->siswa->AsramaPondok ?? '-' }} - {{ $item->siswa->KamarPondok ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-atom text-amber-400"></i>
                        <span>{{ $item->siswa->TingkatDiniyah ?? '-' }} - {{ $item->siswa->KelasDiniyah ?? '' }}</span>
                    </div>
                </div>
                @include('penanganan.partials.status-siswa')
                <div
                    class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">
                    <span class="text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        Dibuat {{ $item->created_at->diffForHumans() }}</span>
                    <span class="text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        Diperbarui {{ $item->lastHistory?->updated_at->diffForHumans() ?? 'Belum diperbarui' }}</span>
                </div>
                @if ($item->kesanggupanTerakhir)
                    <div
                        class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">
                        Sanggup membayar tanggal: {{ $item->kesanggupanTerakhir->tanggal }} dengan nominal:
                        {{ number_format($item->kesanggupanTerakhir->nominal, 0, ',', '.') }}
                    </div>
                @endif

            </div>

            <div class="flex flex-col gap-2 shrink-0">
                <button onclick="syncPembayaran({{ $item->siswa->id }})"
                    class="p-2.5 md:px-3 md:py-1.5 text-blue-600 bg-blue-50 border-2 border-blue-200 rounded-xl hover:bg-blue-100 transition flex items-center justify-center"
                    title="Sync Data">
                    <i class="fas fa-sync-alt text-sm"></i>
                    <span class="inline ml-2 text-[11px] font-bold uppercase">Sync</span>
                </button>
                <a href="{{ route('penanganan.show', $item->siswa->id) }}"
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
        <p class="text-gray-500 font-medium">Data siswa tidak ditemukan.</p>
    </div>
@endforelse
