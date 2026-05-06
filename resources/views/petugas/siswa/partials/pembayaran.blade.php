<div class="bg-white shadow p-5 rounded">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Riwayat Pembayaran</h2>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">Saldo:</span>
            <span class="font-semibold">Rp {{ number_format($siswa->saldo ?? 0, 0, ',', '.') }}</span>
            @if (!$siswa->is_lunas)
                <a href="{{ route('penanganan.show', $siswa->id) }}" class="px-3 py-1 bg-red-500 text-white rounded text-sm">
                    Penanganan
                </a>
            @endif
        </div>
    </div>

    {{-- Ringkasan Per Periode --}}
    @forelse ($summary as $sum)
        <div x-data="{ open: false }" class="border rounded mb-4">
            <button @click="open = !open"
                class="w-full flex justify-between p-4 bg-gray-100 hover:bg-gray-200 transition">
                <span class="font-semibold">Periode: {{ $sum->idperiode }}</span>
                <span class="{{ $sum->lunas ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($sum->total_debet, 0, ',', '.') }} / Rp {{ number_format($sum->total_kredit, 0, ',', '.') }}
                </span>
            </button>

            <div x-show="open" class="p-4 space-y-4">
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong>Tagihan:</strong>
                        <p>Rp {{ number_format($sum->total_kredit, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <strong>Dibayar:</strong>
                        <p>Rp {{ number_format($sum->total_debet, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <strong>Sisa:</strong>
                        <p class="{{ $sum->sisa_tagihan > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($sum->sisa_tagihan, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                @if ($sum->kelas_history)
                    <p class="text-xs text-gray-500">{{ $sum->kelas_history }}</p>
                @endif

                {{-- Detail belum lunas untuk periode ini --}}
                @php
                    $periodItems = collect($belumLunas)->where('idperiode', $sum->idperiode);
                @endphp
                @if ($periodItems->count() > 0)
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="p-2 text-left">Kategori</th>
                                <th class="p-2 text-left">Unit</th>
                                <th class="p-2 text-right">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($periodItems as $item)
                                <tr class="border-b">
                                    <td class="p-2">{{ $item->judul }}</td>
                                    <td class="p-2">{{ $item->nama_unit }}</td>
                                    <td class="p-2 text-right text-red-600 font-semibold">
                                        Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">Tidak ada data pembayaran.</p>
    @endforelse
</div>
