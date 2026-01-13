@extends('layouts.dashboard')

@section('title', 'Tambah Penanganan')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- DATA SISWA --}}<a href="{{ route('penanganan.show', $siswa->id) }}"
            class="inline-flex items-center gap-2 text-sm font-medium
          text-gray-600 hover:text-blue-700
          bg-gray-50 hover:bg-blue-100
          px-4 py-2 rounded-lg
          transition">

            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Riwayat Penanganan
        </a>
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

        {{-- TUNGGAKAN --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold mb-4">Saldo saat ini</h2>
                <p class="text-xl font-bold">
                    Rp {{ number_format($siswa->saldo->saldo ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <h2 class="text-lg font-semibold mb-4">Tunggakan Pembayaran</h2>

            @php
                $groupedByPeriode = collect($kategoriBelumLunas)->groupBy('periode');
            @endphp

            <div class="space-y-6">
                @foreach ($groupedByPeriode as $periode => $itemsPerPeriode)
                    <div class="border rounded-lg p-4">
                        <h3 class="text-blue-600 font-semibold mb-2">
                            Periode {{ $periode }}
                        </h3>

                        <div class="space-y-3">
                            @foreach ($itemsPerPeriode as $item)
                                <div class="">
                                    <p class="font-medium mb-2">
                                        {{ $item['category_name'] }}
                                    </p>

                                    <ul class="space-y-1 text-sm">
                                        @foreach ($item['items'] as $detail)
                                            <li class="flex justify-between">
                                                <span>{{ $detail['unit_name'] ?? 'Tidak diketahui' }}</span>
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
                @endforeach
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


        {{-- FORM PENANGANAN --}}
        <form action="{{ route('penanganan.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_siswa" value="{{ $siswa->id }}">

            <div class="bg-white rounded-lg shadow p-5 space-y-4">
                <h2 class="text-lg font-semibold">Form Penanganan</h2>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jenis Penanganan
                    </label>
                    <select name="jenis_penanganan" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200">
                        <option value="chat">Chat (WhatsApp)</option>
                        <option value="telepon">Telepon</option>
                        <option value="telepon_ulang">Telepon Ulang</option>
                        <option value="visit">Visit</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Catatan
                    </label>
                    <textarea name="catatan" rows="4"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        placeholder="Contoh: WA dikirim, menunggu respon wali."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan Penanganan
                    </button>
                </div>
            </div>
        </form>

    </div>

    {{-- JS --}}
    <script>
        function copyWaMessage() {
            const textarea = document.getElementById('waMessage');
            textarea.select();
            document.execCommand('copy');
            alert('Pesan WhatsApp berhasil disalin');
        }
    </script>
@endsection
