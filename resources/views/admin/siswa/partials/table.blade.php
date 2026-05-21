<div class="md:hidden divide-y divide-slate-200/70">
    @forelse ($siswa as $item)
        @php
            $assigned = $item->petugas->first();
            $hasPenanganan = ($item->jumlah_penanganan ?? 0) > 0;
            $hasActivePenanganan = ($item->penanganan_aktif_count ?? 0) > 0;
            $totalTagihan = (int) ($item->total_tunggakan ?? 0);
        @endphp
        <article class="px-3 py-2.5">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-black text-slate-800">{{ $item->nama }}</p>
                            <span
                                class="shrink-0 font-mono text-[10px] font-bold text-slate-400">{{ $item->idperson }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $totalTagihan > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $totalTagihan > 0 ? 'Rp ' . number_format($totalTagihan, 0, ',', '.') : 'Lunas' }}
                            </span>
                            @if (!$hasPenanganan)
                                <span
                                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">Belum
                                    Ditangani</span>
                            @elseif ($hasActivePenanganan)
                                <span
                                    class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Aktif</span>
                            @else
                                <span
                                    class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Selesai</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.siswa.show', $item->id) }}"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

                <div class="mt-2 grid grid-cols-[1fr_auto] gap-2">
                    <div class="min-w-0 text-[11px] leading-5 text-slate-500 flex gap-2">
                        <p class="truncate"><i
                                class="fas fa-graduation-cap mr-1.5 text-blue-400"></i>{{ $item->unit_formal ?? '-' }} -
                            {{ $item->kelas_formal ?? '-' }}</p>
                        <p class="truncate"><i
                                class="fas fa-home mr-1.5 text-emerald-400"></i>{{ $item->AsramaPondok ?? '-' }} -
                            {{ $item->KamarPondok ?? '-' }}</p>
                        <p class="truncate"><i
                                class="fas fa-atom mr-1.5 text-orange-500"></i>{{ $item->TingkatMadin ?? '-' }} -
                            {{ $item->KelasMadin ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Tagihan</p>
                        <p class="{{ $totalTagihan > 0 ? 'text-rose-600' : 'text-emerald-600' }} text-xs font-black">
                            Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-2 flex items-center justify-between gap-2 border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                    <span class="truncate"><i
                            class="fas fa-user-shield mr-1 text-indigo-400"></i>{{ $assigned ? $assigned->name : 'Belum ada petugas' }}</span>
                    @if ($hasPenanganan)
                        <span class="shrink-0 font-semibold text-slate-400">{{ $item->jumlah_penanganan }}x
                            penanganan</span>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="p-8 text-center text-sm font-semibold text-slate-500">Tidak ada siswa.</div>
    @endforelse
</div>

<div class="hidden overflow-x-auto md:block">
    <table class="min-w-full border-collapse">
        <thead class="bg-slate-50">
            <tr class="text-xs uppercase tracking-wide text-slate-500">
                <th class="px-5 py-4 text-left">Nama</th>
                <th class="px-5 py-4 text-left">Lembaga Formal</th>
                <th class="px-5 py-4 text-left">Pondok</th>
                <th class="px-5 py-4 text-left">Madin</th>
                <th class="px-5 py-4 text-right">Total Tagihan</th>
                <th class="px-5 py-4 text-left">Petugas</th>
                <th class="px-5 py-4 text-left">Penanganan</th>
                <th class="px-5 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200/70">
            @forelse ($siswa as $item)
                @php
                    $assigned = $item->petugas->first();
                    $hasPenanganan = ($item->jumlah_penanganan ?? 0) > 0;
                    $hasActivePenanganan = ($item->penanganan_aktif_count ?? 0) > 0;
                    $totalTagihan = (int) ($item->total_tunggakan ?? 0);
                @endphp
                <tr class="hover:bg-slate-50/80">
                    <td class="px-5 py-4">
                        <div class="font-bold text-slate-800">{{ $item->nama }}</div>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-slate-400">{{ $item->idperson }}</span>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $totalTagihan > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $totalTagihan > 0 ? 'Rp ' . number_format($totalTagihan, 0, ',', '.') : 'Lunas' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">{{ $item->unit_formal ?? 'Tidak ada' }} -
                        {{ $item->kelas_formal ?? 'Tidak ada' }}</td>
                    <td class="px-5 py-4 text-sm text-slate-600">{{ $item->AsramaPondok ?? 'Tidak ada' }} -
                        {{ $item->KamarPondok ?? 'Tidak ada' }}</td>
                    <td class="px-5 py-4 text-sm text-slate-600">{{ $item->TingkatMadin ?? 'Tidak ada' }} -
                        {{ $item->KelasMadin ?? 'Tidak ada' }}</td>
                    <td
                        class="px-5 py-4 text-right text-sm font-black {{ $totalTagihan > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-sm font-semibold text-slate-600">{{ $assigned ? $assigned->name : '-' }}
                    </td>
                    <td class="px-5 py-4">
                        @if (!$hasPenanganan)
                            <span
                                class="inline-flex rounded-full bg-slate-200/80 px-3 py-1 text-xs font-bold text-slate-600">Belum
                                Ditangani</span>
                        @elseif ($hasActivePenanganan)
                            <span
                                class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Sedang
                                Aktif</span>
                        @else
                            <span
                                class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Selesai</span>
                        @endif
                        @if ($hasPenanganan)
                            <span class="ml-1 text-xs text-slate-400">{{ $item->jumlah_penanganan }}x</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <a href="{{ route('admin.siswa.show', $item->id) }}"
                            class="inline-flex items-center rounded-xl bg-indigo-600 px-3 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-sm font-semibold text-slate-500">Tidak ada
                        siswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-4">
    @include('admin.siswa.partials.pagination', ['paginator' => $siswa])
</div>
