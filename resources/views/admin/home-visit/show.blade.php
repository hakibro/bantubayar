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
                        <span
                            class="px-2 py-1 text-xs rounded-full 
                        @if ($homeVisit->status == 'dijadwalkan') bg-yellow-100 text-yellow-800
                        @elseif($homeVisit->status == 'dilaksanakan') bg-blue-100 text-blue-800
                        @elseif($homeVisit->status == 'selesai') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
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
                        <p class="text-sm text-gray-500">Dibuat oleh</p>
                        <p class="font-semibold">{{ $homeVisit->admin->name }}</p>
                    </div>
                </div>

                <!-- Link untuk petugas -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700 mb-2">Link untuk petugas visit:</p>
                    <div class="flex items-center">
                        <input type="text" value="{{ url('/visit/' . $homeVisit->token) }}"
                            class="flex-1 border rounded-l-lg px-3 py-2 text-sm bg-white" readonly>
                        <button onclick="copyToClipboard('{{ url('/visit/' . $homeVisit->token) }}')"
                            class="bg-primary text-white px-4 py-2 rounded-r-lg hover:bg-blue-700">
                            Salin
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Kirim link ini ke petugas via WhatsApp</p>
                </div>

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
                    <a href="{{ route('admin.home-visit.cetak', $homeVisit->id) }}" target="_blank"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        <i class="fas fa-print"></i> Cetak Surat Tugas
                    </a>
                    <a href="{{ route('admin.home-visit.select') }}" class="border px-4 py-2 rounded-lg hover:bg-gray-50">
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
        </script>
    @endpush
@endsection
