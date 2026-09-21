@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h1 class="text-2xl font-bold mb-4">Detail Home Visit</h1>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Siswa</p>
                        <p class="font-semibold">{{ $homeVisit->siswa->nama }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">ID Person</p>
                        <p class="font-semibold">{{ $homeVisit->siswa->idperson }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Petugas Visit</p>
                        <p class="font-semibold">{{ $homeVisit->petugas_nama }} ({{ $homeVisit->petugas_hp }})</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        @php
                            $badge = match ($homeVisit->status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'disetujui' => 'bg-blue-100 text-blue-800',
                                'dilaksanakan' => 'bg-indigo-100 text-indigo-800',
                                'selesai' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'batal' => 'bg-gray-100 text-gray-600',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $badge }}">
                            {{ $homeVisit->status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Visit</p>
                        <p class="font-semibold">
                            {{ $homeVisit->tanggal_visit ? \Carbon\Carbon::parse($homeVisit->tanggal_visit)->format('d/m/Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Diajukan oleh</p>
                        <p class="font-semibold">
                            {{ $homeVisit->pengaju->name ?? ($homeVisit->admin->name ?? '-') }}
                        </p>
                    </div>
                    @if ($homeVisit->penyetuju)
                        <div>
                            <p class="text-sm text-gray-500">Disetujui oleh</p>
                            <p class="font-semibold">{{ $homeVisit->penyetuju->name }}</p>
                        </div>
                    @endif
                    @if ($homeVisit->alasan_pengajuan)
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Alasan Pengajuan</p>
                            <p class="font-semibold">{{ $homeVisit->alasan_pengajuan }}</p>
                        </div>
                    @endif
                </div>

                <!-- Link untuk petugas -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700 mb-2">Link untuk petugas home-visit:</p>
                    <div class="flex items-center">
                        <input type="text" value="{{ url('/visit/' . $homeVisit->token) }}"
                            class="flex-1 border rounded-l-lg px-3 py-2 text-sm bg-white" readonly>
                        <button onclick="copyToClipboard('{{ url('/visit/' . $homeVisit->token) }}')"
                            class="bg-primary text-white px-4 py-2 hover:bg-blue-700">
                            Salin
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button onclick="sendWhatsapp('{{ url('/visit/' . $homeVisit->token) }}')"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fab fa-whatsapp"></i> Kirim via WhatsApp
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Kirim link ini ke petugas home-visit via WhatsApp.</p>
                </div>

                <!-- Aksi approval untuk pengajuan pending -->
                @if ($homeVisit->status === 'pending')
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm font-semibold text-yellow-800 mb-3">Pengajuan ini menunggu persetujuan.</p>
                        <div class="flex gap-2">
                            <form action="{{ route('admin.home-visit.approve', $homeVisit->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                            </form>
                            <form action="{{ route('admin.home-visit.reject', $homeVisit->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                    <i class="fas fa-xmark"></i> Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Jika sudah ada laporan -->
                @if ($homeVisit->laporan)
                    <div class="border-t pt-4 mt-4">
                        <h2 class="font-semibold text-lg mb-3">Laporan Visit</h2>

                        @if (!empty($homeVisit->laporan['foto']))
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-2">Foto Dokumentasi:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach ($homeVisit->laporan['foto'] as $foto)
                                        <img src="{{ Storage::url($foto) }}" class="rounded-lg h-24 w-full object-cover">
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Lokasi</p>
                                <p>{{ $homeVisit->laporan['lokasi'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Hasil</p>
                                <p>{{ $homeVisit->laporan['hasil'] ?? '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-500">Catatan</p>
                                <p>{{ $homeVisit->laporan['catatan'] ?? '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-500">Waktu Lapor</p>
                                <p>{{ isset($homeVisit->laporan['waktu_lapor']) ? \Carbon\Carbon::parse($homeVisit->laporan['waktu_lapor'])->format('d/m/Y H:i') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end space-x-3 mt-6">
                    @if (!in_array($homeVisit->status, ['selesai', 'ditolak', 'batal']))
                        <form action="{{ route('admin.home-visit.batal', $homeVisit->id) }}" method="POST"
                            onsubmit="return confirm('Batalkan home visit ini?')">
                            @csrf
                            <button type="submit" class="border border-red-300 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">
                                <i class="fas fa-ban"></i> Batalkan
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.home-visit.cetak', $homeVisit->id) }}" target="_blank"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        <i class="fas fa-print"></i> Cetak Surat Tugas
                    </a>
                    <a href="{{ route('admin.home-visit.index') }}" class="border px-4 py-2 rounded-lg hover:bg-gray-50">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Link berhasil disalin!');
                }).catch(() => {
                    alert('Gagal menyalin link.');
                });
            }

            function sendWhatsapp(link) {
                let phone = "{{ $homeVisit->petugas_hp ?? '' }}".replace(/[^0-9]/g, '');
                if (phone.startsWith('0')) phone = '62' + phone.slice(1);

                const nama = "{{ $homeVisit->petugas_nama ?? '' }}";
                const siswa = "{{ $homeVisit->siswa->nama }}";
                const pesan = `Assalamu'alaikum ${nama}. Anda ditugaskan melakukan home visit untuk siswa ${siswa}. Silakan buka link berikut untuk melihat detail dan mengisi laporan:\n\n${link}`;

                if (phone && phone.startsWith('62')) {
                    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(pesan)}`, '_blank');
                } else {
                    alert('Nomor HP petugas belum diisi. Silakan salin link secara manual.');
                }
            }
        </script>
    @endpush
@endsection
