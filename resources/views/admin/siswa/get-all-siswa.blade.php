@extends('layouts.dashboard')

@section('title', 'Sinkronisasi Siswa')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        <div class="bg-white rounded-2xl shadow-md border border-gray-100">

            <div class="p-6">
                <h2 class="text-2xl font-semibold text-slate-800 mb-2">Sinkronisasi Data Siswa</h2>
                <p class="text-sm text-slate-500 mb-6">Ambil data siswa terbaru dari API dan simpan ke database. Proses
                    berjalan di background server — progress bar ini adalah dummy (animasi).</p>

                <div class="flex items-center gap-3">
                    <button id="btnSync"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <!-- spinner placeholder (hidden until clicked) -->
                        <svg id="btnSpinner" class="w-4 h-4 text-white hidden animate-spin" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span id="btnLabel">Mulai Sinkronisasi</span>
                    </button>
                    <button id="btnTestApi"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-300">
                        Test API
                    </button>


                    <span id="statusBadge" class="text-sm text-slate-500"></span>
                    <div class="flex items-center gap-3 mt-4">
                        <span id="testApiStatus" class="text-sm text-slate-500"></span>
                    </div>

                    <div id="testApiResult" class="mt-3"></div>
                </div>


                <!-- progress box -->
                <div id="progressBox" class="mt-6 p-4 rounded-lg border border-gray-100 bg-slate-50 hidden">
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-sm font-medium text-slate-700">Proses Sinkronisasi</div>
                        <div id="progressPercent" class="text-sm font-semibold text-slate-700">0%</div>
                    </div>

                    <div class="w-full bg-white border border-gray-200 rounded-full h-4 overflow-hidden">
                        <div id="progressBar"
                            class="h-4 rounded-full bg-emerald-500 w-0 transition-[width] duration-300 ease-out"></div>
                    </div>

                    <p id="progressText" class="mt-3 text-sm text-slate-500">Menunggu...</p>
                </div>

                <!-- hasil -->
                <div id="resultBox" class="mt-4"></div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ----------------------- Test API

            const btnTestApi = document.getElementById('btnTestApi');
            const testApiStatus = document.getElementById('testApiStatus');
            const testApiResult = document.getElementById('testApiResult');

            btnTestApi.addEventListener('click', async function() {
                btnTestApi.disabled = true;
                testApiStatus.textContent = "Menguji API...";
                testApiStatus.className = "text-sm text-slate-600";

                testApiResult.innerHTML = "";

                try {
                    const res = await fetch("{{ url('/admin/siswa/test-api') }}", {
                        method: "GET",
                        headers: {
                            "Accept": "application/json"
                        }
                    });

                    const body = await res.json();

                    if (body.status) {
                        testApiStatus.textContent = "API OK";
                        testApiStatus.className = "text-sm text-emerald-600";

                        testApiResult.innerHTML = `
                <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    <strong>Berhasil:</strong> API merespon dengan benar.<br>
                    Total data terdeteksi: <strong>${body.total ?? '-'}</strong>
                </div>
            `;
                    } else {
                        testApiStatus.textContent = "API Error";
                        testApiStatus.className = "text-sm text-red-600";

                        testApiResult.innerHTML = `
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <strong>Gagal:</strong> ${body.message || "Kesalahan tidak diketahui."}
                </div>
            `;
                    }
                } catch (err) {
                    testApiStatus.textContent = "Network Error";
                    testApiStatus.className = "text-sm text-red-600";

                    testApiResult.innerHTML = `
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                Tidak dapat menghubungi API. Periksa koneksi backend / URL API.
            </div>
        `;
                } finally {
                    btnTestApi.disabled = false;
                }
            });


            // ----------------------- Get Data Siswa
            const btn = document.getElementById('btnSync');
            const btnSpinner = document.getElementById('btnSpinner');
            const btnLabel = document.getElementById('btnLabel');
            const progressBox = document.getElementById('progressBox');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            const progressText = document.getElementById('progressText');
            const statusBadge = document.getElementById('statusBadge');
            const resultBox = document.getElementById('resultBox');

            btn.addEventListener('click', async function() {
                // disable button & show spinner
                btn.disabled = true;
                btnSpinner.classList.remove('hidden');
                btnLabel.textContent = 'Memproses...';
                statusBadge.textContent = '';

                // show progress container
                progressBox.classList.remove('hidden');
                progressText.textContent = 'Mengambil data awal dari server...';

                // dummy progress until 85%
                let dummy = 0;
                const step = 4; // % per tick
                const tickMs = 180; // ms per tick
                const maxDummy = 85; // maximum dummy percent

                const timer = setInterval(() => {
                    if (dummy < maxDummy) {
                        dummy = Math.min(maxDummy, dummy + step);
                        progressBar.style.width = dummy + '%';
                        progressPercent.textContent = dummy + '%';
                    }
                }, tickMs);

                try {
                    const res = await fetch("{{ url('/admin/siswa/get-all-siswa') }}", {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.content || ''
                        }
                    });

                    const body = await res.json();

                    // stop dummy
                    clearInterval(timer);

                    if (body.status) {
                        // success -> progress to 100%
                        progressBar.style.width = '100%';
                        progressPercent.textContent = '100%';
                        progressText.textContent = 'Sinkronisasi selesai. Total: ' + (body.total ??
                            '—') + ' siswa.';
                        statusBadge.textContent = 'Sukses';
                        statusBadge.className = 'text-sm text-emerald-600';

                        // show success message card
                        resultBox.innerHTML = `
          <div class="mt-4 p-3 rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-800 text-sm">
            Sinkronisasi berhasil: <strong>${body.total ?? '—'}</strong> siswa disimpan.
          </div>
        `;
                        // redirect setelah 3 detik
                        setTimeout(() => {
                            window.location.href = "{{ url('/admin/siswa') }}";
                        }, 3000);

                    } else {
                        // error from backend
                        progressBar.style.width = '100%';
                        progressPercent.textContent = 'ERROR';
                        progressText.textContent = 'Gagal: ' + (body.message ?? 'Unknown error');
                        statusBadge.textContent = 'Gagal';
                        statusBadge.className = 'text-sm text-red-600';

                        resultBox.innerHTML = `
          <div class="mt-4 p-3 rounded-lg border border-red-100 bg-red-50 text-red-800 text-sm">
            ${body.message ?? 'Terjadi kesalahan saat sinkronisasi.'}
          </div>
        `;
                    }
                } catch (err) {
                    clearInterval(timer);

                    progressBar.style.width = '100%';
                    progressPercent.textContent = 'ERROR';
                    progressText.textContent = 'Terjadi kesalahan jaringan.';
                    statusBadge.textContent = 'Gagal';
                    statusBadge.className = 'text-sm text-red-600';

                    resultBox.innerHTML = `
        <div class="mt-4 p-3 rounded-lg border border-red-100 bg-red-50 text-red-800 text-sm">
          Terjadi kesalahan jaringan. Periksa koneksi atau coba lagi.
        </div>
      `;
                } finally {
                    // restore button
                    btn.disabled = false;
                    btnSpinner.classList.add('hidden');
                    btnLabel.textContent = 'Mulai Sinkronisasi';
                }
            });
        });
    </script>
@endsection
