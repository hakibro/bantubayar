@extends('layouts.container')
@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Laporan Home Visit</h1>

        <!-- Data Siswa -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="font-semibold text-lg mb-2">Data Siswa</h2>
            <p><span class="text-gray-600">Nama:</span> {{ $homeVisit->siswa->nama }}</p>
            <p><span class="text-gray-600">Alamat:</span> {{ $homeVisit->siswa->AsramaPondok ?? '-' }}, Kamar
                {{ $homeVisit->siswa->KamarPondok ?? '-' }}</p>
            <p><span class="text-gray-600">No. HP Wali:</span> {{ $homeVisit->siswa->phone ?? '-' }}</p>
        </div>

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">{{ session('error') }}</div>
        @endif

        <form action="{{ route('visit.submit', $homeVisit->token) }}" method="POST" enctype="multipart/form-data"
            id="visitForm">
            @csrf

            <!-- ===== UPLOAD FOTO DENGAN PREVIEW ===== -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Dokumentasi</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition cursor-pointer"
                    id="dropZone">
                    <input type="file" name="foto[]" id="fotoInput" multiple accept="image/*" class="hidden">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Klik atau seret foto ke sini</p>
                    <p class="text-xs text-gray-400 mt-1">Maks 2MB per foto, format JPG/PNG</p>
                </div>
                @error('foto.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <!-- Preview Gallery -->
                <div class="grid grid-cols-3 gap-2 mt-4" id="previewGallery"></div>
            </div>

            <!-- Lokasi dengan tombol ambil -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Kunjungan</label>
                <div class="flex gap-2">
                    <input type="text" name="lokasi" id="lokasiInput"
                        class="flex-1 border rounded-lg px-3 py-2 @error('lokasi') border-red-500 @enderror"
                        placeholder="Klik tombol untuk mengambil lokasi" readonly>
                    <button type="button" id="ambilLokasiBtn"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <i class="fas fa-location-dot"></i> Ambil
                    </button>
                </div>
                <div id="lokasiStatus" class="text-xs text-gray-500 mt-1"></div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                @error('lokasi')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="catatan" rows="4"
                    class="w-full border rounded-lg px-3 py-2 @error('catatan') border-red-500 @enderror"
                    placeholder="Deskripsikan hasil kunjungan..."></textarea>
                @error('catatan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hasil -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hasil Kunjungan</label>
                <select name="hasil" id="hasilSelect" required
                    class="w-full border rounded-lg px-3 py-2 @error('hasil') border-red-500 @enderror">
                    <option value="">-- Pilih Hasil --</option>
                    <option value="berhasil">Berhasil (Wali bersedia membayar)</option>
                    <option value="gagal">Gagal (Wali tidak ada)</option>
                    <option value="tidak_ditemukan">Tidak Ditemukan (Alamat tidak ditemukan)</option>
                    <option value="menolak">Menolak (Wali menolak)</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                @error('hasil')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <!-- Input tambahan jika memilih "lainnya" -->
                <div id="hasilLainnyaContainer" class="mt-2 hidden">
                    <input type="text" name="hasil_lainnya" id="hasilLainnya" class="w-full border rounded-lg px-3 py-2"
                        placeholder="Jelaskan hasil lainnya...">
                </div>
            </div>

            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-blue-700">
                Kirim Laporan
            </button>
        </form>
    </div>

    <script>
        // ========== UPLOAD FOTO DENGAN PREVIEW ==========
        const dropZone = document.getElementById('dropZone');
        const fotoInput = document.getElementById('fotoInput');
        const previewGallery = document.getElementById('previewGallery');
        let selectedFiles = []; // Menyimpan file yang dipilih

        // Fungsi untuk memperbarui preview
        function updatePreview() {
            previewGallery.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'relative group';
                    previewItem.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border">
                    <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                    previewGallery.appendChild(previewItem);

                    // Event hapus
                    previewItem.querySelector('button').addEventListener('click', function() {
                        const idx = parseInt(this.dataset.index);
                        selectedFiles.splice(idx, 1);
                        updatePreview();
                        // Update file input (reset dan tambah ulang)
                        updateFileInput();
                    });
                };
                reader.readAsDataURL(file);
            });
        }

        // Update file input dengan FileList buatan
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fotoInput.files = dataTransfer.files;
        }

        // Klik drop zone untuk membuka file dialog
        dropZone.addEventListener('click', () => fotoInput.click());

        // Drag & drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary', 'bg-blue-50');
        });
        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-blue-50');
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-blue-50');
            const files = Array.from(e.dataTransfer.files);
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                } else {
                    alert('Hanya file gambar yang diperbolehkan.');
                }
            });
            updatePreview();
            updateFileInput();
        });

        // Jika user memilih file via input
        fotoInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            files.forEach(file => selectedFiles.push(file));
            updatePreview();
            // Tidak perlu updateFileInput karena sudah terisi otomatis, tapi kita perlu sync dengan selectedFiles
            // Sebenarnya file input sudah berisi, tapi jika kita hapus via preview, file input perlu diupdate
            // Jadi kita selalu update selectedFiles berdasarkan input, tapi kita juga ingin menghapus, jadi lebih baik gunakan selectedFiles sebagai sumber utama.
            // Setelah ini, kita set file input dengan selectedFiles.
            updateFileInput();
        });

        // ========== LOKASI ==========
        document.getElementById('ambilLokasiBtn').addEventListener('click', function() {
            const statusEl = document.getElementById('lokasiStatus');
            const lokasiInput = document.getElementById('lokasiInput');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            if (!navigator.geolocation) {
                statusEl.innerText = 'Geolocation tidak didukung browser ini.';
                return;
            }

            statusEl.innerText = 'Mendapatkan lokasi...';
            lokasiInput.value = '';
            latInput.value = '';
            lngInput.value = '';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    latInput.value = lat;
                    lngInput.value = lng;

                    fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.display_name) {
                                lokasiInput.value = data.display_name;
                                statusEl.innerText = 'Lokasi berhasil didapatkan.';
                            } else {
                                lokasiInput.value = `${lat}, ${lng}`;
                                statusEl.innerText = 'Tidak dapat memperoleh alamat, koordinat tersimpan.';
                            }
                        })
                        .catch(error => {
                            console.error('Reverse geocoding error:', error);
                            lokasiInput.value = `${lat}, ${lng}`;
                            statusEl.innerText = 'Gagal mendapatkan alamat, koordinat tersimpan.';
                        });
                },
                function(error) {
                    let msg = 'Gagal mendapatkan lokasi: ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            msg += 'Izin ditolak.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            msg += 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            msg += 'Waktu permintaan habis.';
                            break;
                        default:
                            msg += 'Kesalahan tidak diketahui.';
                    }
                    statusEl.innerText = msg;
                }
            );
        });

        // ========== HASIL LAINNYA ==========
        const hasilSelect = document.getElementById('hasilSelect');
        const hasilLainnyaContainer = document.getElementById('hasilLainnyaContainer');
        const hasilLainnyaInput = document.getElementById('hasilLainnya');

        hasilSelect.addEventListener('change', function() {
            if (this.value === 'lainnya') {
                hasilLainnyaContainer.classList.remove('hidden');
                hasilLainnyaInput.setAttribute('required', 'required');
            } else {
                hasilLainnyaContainer.classList.add('hidden');
                hasilLainnyaInput.removeAttribute('required');
                hasilLainnyaInput.value = '';
            }
        });
    </script>
@endsection
