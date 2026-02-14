{{-- Info Rentang Waktu --}}
<div class="mb-4 flex items-center gap-2 text-xs text-slate-400">
    <i class="fas fa-calendar-alt"></i>
    <span>Menampilkan data:
        <strong>
            {{ $range == 'current_week'
                ? 'Senin - Minggu ini'
                : ($range == 'last_week'
                    ? 'Senin - Minggu lalu'
                    : 'Sebelumnya') }}
        </strong>
    </span>
</div>
{{-- 1. RINGKASAN STATS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @php
        $cards = [
            ['Total', $summary['total'], 'bg-blue-600', 'fa-folder'],
            ['Menunggu', $summary['menunggu_respon'], 'bg-amber-500', 'fa-clock'],
            ['Kesanggupan', $summary['menunggu_tindak_lanjut'], 'bg-rose-500', 'fa-times-circle'],
            ['Selesai', $summary['selesai'], 'bg-emerald-500', 'fa-check-circle'],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-2 -top-2 opacity-5 group-hover:scale-110 transition-transform">
                <i class="fas {{ $card[3] }} text-6xl"></i>
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $card[0] }}</p>
            <div class="flex items-end gap-2 mt-1">
                <span class="text-3xl font-bold text-slate-800">{{ $card[1] }}</span>
            </div>
            <div class="mt-3 w-8 h-1 {{ $card[2] }} rounded-full"></div>
        </div>
    @endforeach
</div>
