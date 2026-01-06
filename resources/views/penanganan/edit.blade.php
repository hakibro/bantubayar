@extends('layouts.dashboard')

@section('title', 'Edit Penanganan')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- DATA SISWA --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-semibold mb-3">Data Siswa</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama</p>
                    <p class="font-medium">{{ $siswa->nama }}</p>
                </div>
                <div>
                    <p class="text-gray-500">ID Siswa</p>
                    <p class="font-medium">{{ $siswa->idperson }}</p>
                </div>
                <div>
                    <p class="text-gray-500">No. HP</p>
                    <p class="font-medium">{{ $siswa->phone ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- TUNGGAKAN (READ ONLY) --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-semibold mb-4">Tunggakan Pembayaran</h2>

            @php
                $groupedByPeriode = collect($kategoriBelumLunas)->groupBy('periode');
            @endphp

            <div class="space-y-6">
                @forelse ($groupedByPeriode as $periode => $itemsPerPeriode)
                    <div class="border rounded-lg p-4">
                        <h3 class="text-blue-600 font-semibold mb-2">
                            Periode {{ $periode }}
                        </h3>

                        <div class="space-y-3">
                            @foreach ($itemsPerPeriode as $item)
                                <div>
                                    <p class="font-medium mb-2">
                                        {{ $item['category_name'] }}
                                    </p>

                                    <ul class="space-y-1 text-sm">
                                        @foreach ($item['items'] as $detail)
                                            <li class="flex justify-between">
                                                <span>{{ $detail['unit_name'] ?? '-' }}</span>
                                                <span class="text-red-600 font-semibold">
                                                    Rp {{ number_format($detail['remaining_balance'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada tunggakan aktif.</p>
                @endforelse
            </div>
        </div>

        {{-- PESAN WHATSAPP --}}
        @php
            $waMessage = "Assalamu’alaikum Wr. Wb.\n\n";
            $waMessage .= "Yth. Bapak/Ibu/Ananda {$siswa->nama}\n\n";
            $waMessage .= "Kami informasikan terdapat tunggakan pembayaran sebagai berikut:\n\n";

            $groupedByPeriode = collect($kategoriBelumLunas)->groupBy('periode');

            foreach ($groupedByPeriode as $periode => $itemsPerPeriode) {
                $waMessage .= "Periode {$periode}\n";

                foreach ($itemsPerPeriode as $item) {
                    $waMessage .= "- {$item['category_name']}\n";

                    foreach ($item['items'] as $detail) {
                        $waMessage .=
                            "  • {$detail['unit_name']} : Rp " .
                            number_format($detail['remaining_balance'] ?? 0, 0, ',', '.') .
                            "\n";
                    }
                }

                $waMessage .= "\n";
            }

            $waMessage .= "Pembayaran dapat dilakukan melalui aplikasi NgalaH Mobile.\n";
            $waMessage .= "Jika mengalami kendala, silakan menghubungi kami melalui WA ini.\n\n";
            $waMessage .= "Terima kasih.\n";

            // Ambil hanya angka
            $phoneRaw = $siswa->phone ?? '';
            $phone = preg_replace('/[^0-9]/', '', $phoneRaw);

            // Normalisasi ke format internasional
            if (str_starts_with($phone, '08')) {
                $phone = '62' . substr($phone, 1);
            }

            // Validasi sederhana (minimal 10 digit)
            $isValidPhone = strlen($phone) >= 10;

            $waUrl = $isValidPhone ? 'https://wa.me/' . $phone . '?text=' . urlencode($waMessage) : null;
        @endphp

        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-semibold mb-3">Pesan WhatsApp</h2>

            <textarea id="waMessage" rows="10" readonly class="w-full border rounded-lg p-3 text-sm bg-gray-50">{{ $waMessage }}</textarea>

            <div class="flex flex-wrap gap-3 mt-4" x-data="{ openQrPesan: false, openQrCall: false }">

                @if (!empty($phone))
                    <a href="{{ $waUrl }}" target="_blank"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Kirim via WhatsApp
                    </a>

                    <button type="button" @click="openQrPesan = true"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                        QR Pesan WA
                    </button>

                    <button type="button" @click="openQrCall = true"
                        class="px-4 py-2 border border-emerald-300 text-emerald-700 rounded-lg hover:bg-emerald-50 transition">
                        QR Telepon WA
                    </button>
                @else
                    <p class="text-sm text-red-600 mt-2">
                        Nomor HP siswa belum tersedia.
                    </p>
                @endif

                <button type="button" onclick="copyWaMessage()"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                    Salin Pesan
                </button>

                {{-- MODAL --}}
                @if (!empty($phone))
                    @include('penanganan.partials.popup-wa-qr-pesan')
                    @include('penanganan.partials.popup-wa-qr-call')
                @endif
            </div>


        </div>

        {{-- FORM UPDATE PENANGANAN --}}
        <form action="{{ route('penanganan.update', $penanganan->id) }}" method="POST" enctype="multipart/form-data"
            x-data="{
                hasil: '{{ old('hasil', $penanganan->hasil) }}',
                preview: '{{ $penanganan->bukti_pembayaran ? asset('storage/' . $penanganan->bukti_pembayaran) : '' }}',
                fileSelected: false,
                filePasted: false
            }" x-init="$nextTick(() => ready = true);
            
            $watch('hasil', value => {
                if (!ready) return;
            
                if (value !== 'lunas' && value !== 'isi_saldo') {
                    preview = '';
                    if ($refs.file) $refs.file.value = null;
                }
            })">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow p-5 space-y-4">
                <h2 class="text-lg font-semibold">Edit Penanganan</h2>

                {{-- JENIS PENANGANAN --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jenis Penanganan
                    </label>
                    <select name="jenis_penanganan" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                        @foreach (['chat', 'telepon', 'telepon_ulang', 'visit'] as $jenis)
                            <option value="{{ $jenis }}" @selected($penanganan->jenis_penanganan === $jenis)>
                                {{ ucfirst(str_replace('_', ' ', $jenis)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- CATATAN --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Catatan
                    </label>
                    <textarea name="catatan" rows="4"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" placeholder="Catatan tambahan...">{{ old('catatan', $penanganan->catatan) }}</textarea>
                </div>

                {{-- HASIL --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Hasil Penanganan
                    </label>
                    <select name="hasil" x-model="hasil"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                        <option value="">-- Pilih hasil --</option>
                        <option value="lunas">Lunas</option>
                        <option value="isi_saldo">Isi Saldo</option>
                        <option value="rekomendasi">Rekomendasi</option>
                        <option value="tidak_ada_respon">Tidak Ada Respon</option>
                    </select>
                </div>

                {{-- BUKTI PEMBAYARAN --}}
                <template x-if="hasil === 'lunas' || hasil === 'isi_saldo'">
                    <div class="relative">

                        <label class="block text-sm font-medium mb-1">
                            Bukti Pembayaran <span class="text-red-500">*</span>
                        </label>

                        {{-- HIDDEN INPUT FOR PASTE --}}
                        <input type="file" name="bukti_pembayaran" accept="image/png,image/jpeg,image/webp"
                            class="hidden" x-ref="file"
                            @change="
    if ($event.target.files.length) {
        fileSelected = true;
        filePasted = true;
        preview = URL.createObjectURL($event.target.files[0]);
    }
">

                        {{-- AREA UI WITH PASTE SUPPORT --}}
                        <div class="border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:bg-gray-50"
                            @click="$refs.file.click()"
                            @dragover.prevent="$el.classList.add('bg-blue-50')"
                            @dragleave.prevent="$el.classList.remove('bg-blue-50')"
                            @drop.prevent="
        $el.classList.remove('bg-blue-50');
        const files = $event.dataTransfer.files;
        if (files.length > 0) {
            $refs.file.files = files;
            const event = new Event('change', { bubbles: true });
            $refs.file.dispatchEvent(event);
        }
    "
                            @paste.prevent="
        const items = $event.clipboardData.items;
        for (let i = 0; i < items.length; i++) {
            if (items[i].kind === 'file' && items[i].type.startsWith('image/')) {
                const file = items[i].getAsFile();
                if (file && file.size > 0) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        $refs.file.files = dt.files;
                    } catch (e) {
                        console.warn('DataTransfer failed, using fallback');
                    }
                    const event = new Event('change', { bubbles: true });
                    $refs.file.dispatchEvent(event);
                    break;
                }
            }
        }
    ">

                            <template x-if="!preview">
                                <div class="text-sm text-gray-500">
                                    <p><b>Klik</b> untuk upload</p>
                                    <p class="text-xs mt-1">atau <b>Ctrl + V</b> untuk paste</p>
                                </div>
                            </template>

                            <template x-if="preview">
                                <img :src="preview"
                                    class="mx-auto max-h-48 max-w-full object-contain rounded-lg shadow">
                            </template>
                        </div>

                        <template x-if="filePasted && preview">
                            <p class="text-xs text-green-600 mt-2 font-semibold">
                                ✓ File siap disimpan
                            </p>
                        </template>

                        @if ($penanganan->bukti_pembayaran)
                            <p class="text-xs text-gray-500 mt-2">
                                Bukti lama akan diganti jika upload baru
                            </p>
                        @endif
                    </div>
                </template>


                {{-- TANGGAL REKOMENDASI --}}
                <div x-show="hasil === 'rekomendasi'" x-transition x-cloak>
                    <label class="block text-sm font-medium mb-1">
                        Tanggal Rekomendasi
                    </label>
                    <input type="date" name="tanggal_rekom"
                        value="{{ old('tanggal_rekom', $penanganan->tanggal_rekom) }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                </div>


                {{-- STATUS (READ ONLY) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Status
                    </label>
                    <input type="text" readonly value="{{ str_replace('_', ' ', ucfirst($penanganan->status)) }}"
                        class="w-full bg-gray-100 border rounded-lg px-3 py-2 text-sm text-gray-600">
                </div>

                {{-- ACTION --}}
                <div class="flex justify-between items-center pt-4">
                    <a href="{{ route('penanganan.siswa', $siswa->id) }}"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100 transition">
                        Kembali
                    </a>

                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Penanganan
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
