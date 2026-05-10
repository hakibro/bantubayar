@forelse ($petugasPerformance as $item)
    <div class="rounded-2xl border border-slate-200 bg-white p-3">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-black text-slate-800">{{ $item->name }}</p>
                <p class="text-xs font-semibold text-slate-400">{{ number_format($item->total, 0, ',', '.') }} penanganan</p>
            </div>
            <p class="shrink-0 text-sm font-black text-indigo-600">{{ number_format($item->percentage, 1, ',', '.') }}%</p>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $item->percentage) }}%"></div>
        </div>
    </div>
@empty
    <div class="rounded-2xl border border-dashed border-slate-200 bg-white/70 p-5 text-center text-sm font-semibold text-slate-400">
        Belum ada penanganan pada filter ini.
    </div>
@endforelse
