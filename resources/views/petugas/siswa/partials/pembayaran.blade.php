<div class="bg-white shadow p-5 rounded">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h2>
        @php
            $belumLunas = $siswa->getKategoriBelumLunas();
        @endphp

        @if (is_array($belumLunas) && count($belumLunas) > 0)
            <a href="{{ route('penanganan.siswa', $siswa->id) }}" class="px-3 py-1 bg-red-500 text-white rounded">
                Penanganan
            </a>
        @endif

    </div>

    @foreach ($siswa->pembayaran as $pay)
        <div x-data="{ open: false }" class="border rounded mb-4">

            <button @click="open = !open"
                class="w-full flex justify-between p-4 bg-gray-100 hover:bg-gray-200 transition">
                <span class="font-semibold">Periode: {{ $pay->periode }}</span>

                @php $s = $pay->data['summary']; @endphp

                <span class="{{ $s['fully_paid'] ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($s['total_paid']) }} / {{ number_format($s['total_billed']) }}
                </span>
            </button>

            <div x-show="open" class="p-4 space-y-4">

                {{-- SUMMARY --}}
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong>Total Bayar:</strong>
                        <p>{{ number_format($s['total_billed']) }}</p>
                    </div>
                    <div>
                        <strong>Total Tagihan:</strong>
                        <p>{{ number_format($s['total_paid']) }}</p>
                    </div>
                    <div>
                        <strong>Sisa:</strong>
                        <p class="{{ $s['total_remaining'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($s['total_remaining']) }}
                        </p>
                    </div>
                </div>

                {{-- CATEGORIES --}}
                @foreach ($pay->data['categories'] as $cat)
                    <div class="border rounded p-4">
                        <h3 class="font-semibold mb-2">{{ $cat['category_name'] }}</h3>

                        {{-- ITEMS --}}
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left">Unit</th>
                                    <th class="p-2 text-left">Bayar</th>
                                    <th class="p-2 text-left">Tagihan</th>
                                    <th class="p-2 text-left">Sisa</th>
                                    <th class="p-2 text-left">Status</th>
                                    <th class="p-2 text-left">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cat['items'] as $item)
                                    <tr class="border-b">
                                        <td class="p-2">{{ $item['unit_name'] }}</td>
                                        <td class="p-2">{{ number_format($item['amount_billed']) }}</td>
                                        <td class="p-2">{{ number_format($item['amount_paid']) }}</td>
                                        <td class="p-2">{{ number_format($item['remaining_balance']) }}</td>
                                        <td class="p-2">
                                            <span
                                                class="px-2 py-1 text-xs rounded
                                                {{ $item['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ ucfirst($item['payment_status']) }}
                                            </span>
                                        </td>
                                        <td class="p-2">{{ $item['journal_date'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                @endforeach

            </div>
        </div>
    @endforeach
</div>
