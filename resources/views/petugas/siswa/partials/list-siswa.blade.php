@forelse ($siswa as $item)
    <div id="siswa-{{ $item->id }}"
        class="group {{ $item->sedangDitangani()
            ? 'bg-yellow-50 ring-2 ring-yellow-200 text-gray-800'
            : ($item->penangananLunas() &&
            $item->getTotalTunggakan() == 0 &&
            $item->penangananLunas()->updated_at->isSameMonth(now())
                ? 'bg-gray-100 border-none shadow-none text-gray-400'
                : 'bg-white') }} rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all p-4">

        <div class="flex justify-between items-center gap-3">
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-base leading-tight truncate">
                    {{ $item->nama }}
                </h3>
                <div class="flex items-center gap-2">


                </div>

                <div
                    class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">

                    <span class="text-xs font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        {{ $item->idperson }}
                    </span>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-school text-blue-400"></i>
                        <span>{{ $item->UnitFormal ?? '-' }} ({{ $item->KelasFormal ?? '-' }})</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-bed text-green-400"></i>
                        <span>{{ $item->AsramaPondok ?? '-' }}/{{ $item->KamarPondok ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-mosque text-amber-400"></i>
                        <span>{{ $item->TingkatDiniyah ?? '-' }}</span>
                    </div>
                </div>
                @include('petugas.siswa.partials.status-siswa')



            </div>

            <div class="flex flex-col gap-2 shrink-0">
                <button onclick="syncPembayaran({{ $item->id }})"
                    class="p-2.5 md:px-3 md:py-1.5 text-blue-600 bg-blue-50 border-2 border-blue-200 rounded-xl hover:bg-blue-100 transition flex items-center justify-center"
                    title="Sync Data">
                    <i class="fas fa-sync-alt text-sm"></i>
                    <span class="inline ml-2 text-[11px] font-bold uppercase">Sync</span>
                </button>
                <a href="{{ route('penanganan.show', $item->id) }}"
                    class="p-2.5 md:px-4 md:py-1.5 bg-gray-800 text-white rounded-xl hover:bg-black transition flex items-center justify-center shadow-sm"
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
