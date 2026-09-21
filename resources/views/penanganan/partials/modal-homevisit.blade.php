<!-- Modal Home Visit (Pengajuan) -->
<div id="modalHomevisit"
    class="fixed inset-0 bg-black/50 z-50 hidden flex items-end md:items-center justify-center transition-opacity">
    <div class="bg-white w-full md:w-[520px] md:rounded-3xl rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300 max-h-[90vh] overflow-y-auto"
        id="cardHomevisit">
        <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-6 md:hidden"></div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Pengajuan Home Visit</h3>
            <button onclick="closeModal('homevisit')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>

        <div id="homevisitLoading" class="py-10 text-center text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl"></i>
            <p class="text-sm mt-2">Memeriksa kelayakan...</p>
        </div>

        <div id="homevisitContent" class="hidden space-y-5">
            <!-- Status pengajuan aktif -->
            <div id="homevisitPengajuanAktif" class="hidden"></div>

            <!-- Checklist syarat -->
            <div>
                <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Kategori Sulit Terpenuhi</p>
                <div id="syaratTerpenuhi" class="space-y-2"></div>
            </div>

            <div id="syaratBelumWrapper">
                <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Belum Terpenuhi</p>
                <div id="syaratBelum" class="space-y-2"></div>
            </div>

            <!-- Override manual -->
            <div id="overrideWrapper" class="hidden">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 mb-2 flex items-start gap-2">
                    <i class="fas fa-triangle-exclamation mt-0.5"></i>
                    <p>Siswa belum memenuhi kategori otomatis. Isi alasan untuk mengajukan secara manual.</p>
                </div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Alasan Pengajuan (wajib bila override)</label>
                <textarea id="homevisitAlasan" rows="3"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                    placeholder="Jelaskan alasan home visit diperlukan..."></textarea>
            </div>

            <!-- Data petugas (opsional) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nama Petugas (opsional)</label>
                    <input type="text" id="homevisitPetugasNama"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                        placeholder="Kosongkan bila cukup share link">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">HP Petugas (opsional)</label>
                    <input type="text" id="homevisitPetugasHp"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                        placeholder="08...">
                </div>
            </div>

            <button id="homevisitSubmit" onclick="submitHomeVisit()"
                class="w-full bg-amber-500 text-white py-3 rounded-xl font-bold shadow-md hover:bg-amber-600 transition">
                Ajukan Home Visit
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const HOMEVISIT_STATUS_URL = "{{ route('penanganan.home_visit.status', $siswa->id) }}";
        const HOMEVISIT_AJUKAN_URL = "{{ route('penanganan.home_visit.ajukan') }}";
        let homeVisitEligible = false;

        function openModalHomeVisit() {
            openModal('homevisit');
            document.getElementById('homevisitLoading').classList.remove('hidden');
            document.getElementById('homevisitContent').classList.add('hidden');
            document.getElementById('homevisitPengajuanAktif').classList.add('hidden');

            fetch(HOMEVISIT_STATUS_URL)
                .then(res => res.json())
                .then(data => renderHomeVisitStatus(data))
                .catch(() => showToast('Gagal memuat status home visit', 'error'));
        }

        function renderHomeVisitStatus(data) {
            document.getElementById('homevisitLoading').classList.add('hidden');
            document.getElementById('homevisitContent').classList.remove('hidden');

            if (!data.success) {
                showToast(data.message ?? 'Gagal memuat status', 'error');
                return;
            }

            homeVisitEligible = data.eligible;

            // Pengajuan aktif
            const pa = document.getElementById('homevisitPengajuanAktif');
            if (data.pengajuan) {
                pa.classList.remove('hidden');
                const badge = data.pengajuan.status === 'disetujui' ?
                    'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                pa.innerHTML = `
                    <div class="border rounded-xl p-3 ${badge}">
                        <p class="text-sm font-bold">Pengajuan aktif: ${data.pengajuan.status.toUpperCase()}</p>
                        <p class="text-xs mt-1">Diajukan ${data.pengajuan.tanggal}</p>
                    </div>`;
                document.getElementById('homevisitSubmit').style.display = 'none';
            }

            // Syarat terpenuhi
            const tw = document.getElementById('syaratTerpenuhi');
            if (data.syarat_terpenuhi.length === 0) {
                tw.innerHTML = `<p class="text-xs text-gray-400 italic">Belum ada kategori otomatis yang terpenuhi.</p>`;
            } else {
                tw.innerHTML = data.syarat_terpenuhi.map(s => `
                    <div class="flex items-start gap-2 text-sm bg-green-50 border border-green-100 rounded-lg p-2">
                        <i class="fas fa-circle-check text-green-500 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-gray-700">${s.label}</p>
                            <p class="text-xs text-gray-500">${s.keterangan}</p>
                        </div>
                    </div>`).join('');
            }

            // Syarat belum
            const bw = document.getElementById('syaratBelum');
            bw.innerHTML = data.syarat_belum.map(s => `
                <div class="flex items-start gap-2 text-sm bg-gray-50 border border-gray-100 rounded-lg p-2">
                    <i class="fas fa-circle-xmark text-gray-300 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-500">${s.label}</p>
                        <p class="text-xs text-gray-400">${s.keterangan}</p>
                    </div>
                </div>`).join('');

            // Override
            const ow = document.getElementById('overrideWrapper');
            if (data.boleh_override && !data.pengajuan) {
                ow.classList.remove('hidden');
            } else {
                ow.classList.add('hidden');
            }
        }

        function submitHomeVisit() {
            const alasan = document.getElementById('homevisitAlasan').value.trim();

            if (!homeVisitEligible && !alasan) {
                showToast('Isi alasan untuk mengajukan secara manual', 'warning');
                return;
            }

            fetch(HOMEVISIT_AJUKAN_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id_siswa: {{ $siswa->id }},
                        alasan_override: alasan,
                        petugas_nama: document.getElementById('homevisitPetugasNama').value,
                        petugas_hp: document.getElementById('homevisitPetugasHp').value,
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then(data => {
                    showToast(data.message ?? 'Pengajuan berhasil', 'success');
                    closeModal('homevisit');
                    setTimeout(() => location.reload(), 900);
                })
                .catch(error => {
                    showToast(error.message ?? 'Terjadi kesalahan', 'error');
                });
        }
    </script>
@endpush
