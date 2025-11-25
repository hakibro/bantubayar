@extends('layouts.dashboard')

@section('title', 'Sinkronisasi Pembayaran Siswa')

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Sinkronisasi Pembayaran Siswa</h1>
                    <p class="text-sm text-gray-600 mt-2">Monitor dan kelola proses sinkronisasi data pembayaran dari API
                        eksternal</p>
                </div>
                <a href="{{ route('admin.siswa.index') }}"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    ← Kembali
                </a>
            </div>
        </div>

        <!-- Main Container -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Control & Status (2 cols) -->
            <div class="lg:col-span-2">
                <!-- Control Card -->
                <div class="bg-white rounded-lg shadow border border-gray-100 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Kontrol Sinkronisasi</h2>

                    <!-- Status Badge -->
                    <div class="mb-6">
                        <div id="statusBadge"
                            class="inline-block px-4 py-2 rounded-full text-white font-semibold
                            @if ($isRunning) bg-blue-500 @else bg-green-500 @endif">
                            @if ($isRunning)
                                🔄 Sedang Berjalan
                            @else
                                ✓ Siap
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mb-8">
                        <button id="startBtn"
                            class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            @if ($isRunning) disabled @endif>
                            <i class="fas fa-play"></i> Mulai Sinkronisasi
                        </button>

                        <button id="cancelBtn"
                            class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            @if (!$isRunning) disabled @endif>
                            <i class="fas fa-stop"></i> Batalkan
                        </button>

                        <button id="resetBtn"
                            class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 flex items-center gap-2">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>

                    <div class="border-t pt-6">
                        <p class="text-sm text-gray-600 mb-3">
                            💡 <strong>Tips:</strong> Pastikan queue worker sedang berjalan di terminal lain sebelum memulai
                            sinkronisasi.
                        </p>
                        <p class="text-sm text-gray-500">
                            Run: <code class="bg-gray-100 px-2 py-1 rounded text-xs">php artisan queue:work database
                                --queue=sync-pembayaran</code>
                        </p>
                    </div>
                </div>

                <!-- Progress Card -->
                <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Progress Sinkronisasi</h2>

                    <!-- Main Progress Bar -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-semibold text-gray-700">Persentase Selesai</span>
                            <span id="progressPercent" class="text-2xl font-bold text-blue-600">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div id="progressBar"
                                class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-500"
                                style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Total -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <p class="text-xs font-semibold text-blue-600 uppercase">Total Siswa</p>
                            <p id="totalCount" class="text-3xl font-bold text-blue-700 mt-2">{{ $totalSiswaSync }}</p>
                        </div>

                        <!-- Berhasil -->
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <p class="text-xs font-semibold text-green-600 uppercase">Berhasil</p>
                            <p id="successCount" class="text-3xl font-bold text-green-700 mt-2">0</p>
                        </div>

                        <!-- Gagal -->
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <p class="text-xs font-semibold text-red-600 uppercase">Gagal</p>
                            <p id="failedCount" class="text-3xl font-bold text-red-700 mt-2">0</p>
                        </div>
                    </div>

                    <!-- Detailed Info -->
                    <div class="mt-8 pt-6 border-t">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Diproses:</span>
                                <span class="font-semibold text-gray-800"><span id="processedCount">0</span> / <span
                                        id="totalCountDetail">{{ $totalSiswaSync }}</span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estimasi Waktu Tersisa:</span>
                                <span class="font-semibold text-gray-800" id="timeRemaining">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span id="syncStatus" class="font-semibold text-blue-600">Menunggu...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Info & Log -->
            <div>
                <!-- Info Card -->
                <div class="bg-white rounded-lg shadow border border-gray-100 p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi</h3>

                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-600 font-semibold">Total Siswa di Database</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $totalSiswa }}</p>
                        </div>

                        <div class="border-t pt-4">
                            <p class="text-gray-600 font-semibold mb-2">Fitur Sinkronisasi:</p>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1 text-xs"></i>
                                    <span>Ambil data pembayaran via API</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1 text-xs"></i>
                                    <span>Update kolom pembayaran JSON</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1 text-xs"></i>
                                    <span>Proses background via queue</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-1 text-xs"></i>
                                    <span>Monitoring real-time progress</span>
                                </li>
                            </ul>
                        </div>

                        <div class="border-t pt-4">
                            <p class="text-gray-600 font-semibold mb-2">Status Terakhir:</p>
                            <p id="lastStatusInfo" class="text-xs text-gray-500 font-mono">
                                @if ($processedSiswa > 0)
                                    Processed: {{ $processedSiswa }}, Failed: {{ $failedSiswa }}
                                @else
                                    Belum ada sinkronisasi
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Log Aktivitas</h3>

                    <div id="activityLog" class="space-y-2 max-h-64 overflow-y-auto text-xs font-mono">
                        <div class="text-gray-500">[{{ now()->format('H:i:s') }}] Halaman dimuat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== VARIABLES =====
        let isRunning = {{ $isRunning ? 'true' : 'false' }};
        let startTime = null;
        let pollingInterval = null;
        let activityLogs = [];

        // ===== UTILITY FUNCTIONS =====
        function addLog(message) {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = `[${timestamp}] ${message}`;
            activityLogs.unshift(logEntry);
            if (activityLogs.length > 50) {
                activityLogs.pop();
            }
            updateActivityLog();
        }

        function updateActivityLog() {
            const logContainer = document.getElementById('activityLog');
            logContainer.innerHTML = activityLogs.map(log => `<div class="text-gray-600">${log}</div>`).join('');
            logContainer.scrollTop = 0;
        }

        function formatTimeRemaining(seconds) {
            if (seconds <= 0) return '-';
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes}m ${secs}s`;
        }

        function updateStatus(running) {
            isRunning = running;
            const badge = document.getElementById('statusBadge');
            const startBtn = document.getElementById('startBtn');
            const cancelBtn = document.getElementById('cancelBtn');

            if (running) {
                badge.textContent = '🔄 Sedang Berjalan';
                badge.className = 'inline-block px-4 py-2 rounded-full text-white font-semibold bg-blue-500';
                startBtn.disabled = true;
                cancelBtn.disabled = false;
            } else {
                badge.textContent = '✓ Siap';
                badge.className = 'inline-block px-4 py-2 rounded-full text-white font-semibold bg-green-500';
                startBtn.disabled = false;
                cancelBtn.disabled = true;
            }
        }

        // ===== FETCH PROGRESS =====
        async function fetchProgress() {
            try {
                const response = await fetch('{{ route('admin.sync-pembayaran.progress') }}');
                const data = await response.json();

                const {
                    total,
                    processed,
                    failed,
                    percent,
                    isRunning: running,
                    successCount
                } = data;

                // Update counters
                document.getElementById('totalCount').textContent = total;
                document.getElementById('processedCount').textContent = processed;
                document.getElementById('successCount').textContent = successCount;
                document.getElementById('failedCount').textContent = failed;
                document.getElementById('totalCountDetail').textContent = total;

                // Update progress bar
                const progressBar = document.getElementById('progressBar');
                const progressPercent = document.getElementById('progressPercent');
                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent.toFixed(1) + '%';

                // Calculate time remaining
                if (running && startTime && processed > 0) {
                    const elapsed = (Date.now() - startTime) / 1000;
                    const avgTime = elapsed / processed;
                    const remaining = Math.ceil((total - processed) * avgTime);
                    document.getElementById('timeRemaining').textContent = formatTimeRemaining(remaining);
                } else {
                    document.getElementById('timeRemaining').textContent = '-';
                }

                // Update sync status
                const statusEl = document.getElementById('syncStatus');
                if (running) {
                    statusEl.textContent = 'Sedang Berjalan...';
                    statusEl.className = 'font-semibold text-blue-600';
                } else if (percent === 100 && total > 0) {
                    statusEl.textContent = 'Selesai';
                    statusEl.className = 'font-semibold text-green-600';
                    addLog(`✓ Sinkronisasi selesai - ${successCount} berhasil, ${failed} gagal`);
                    stopPolling();
                } else {
                    statusEl.textContent = 'Menunggu...';
                    statusEl.className = 'font-semibold text-gray-600';
                }

                // Update status badges
                updateStatus(running);

            } catch (error) {
                console.error('Error fetching progress:', error);
            }
        }

        // ===== START SYNC =====
        document.getElementById('startBtn').addEventListener('click', async function() {
            if (isRunning) return;

            try {
                startTime = Date.now();
                const response = await fetch('{{ route('admin.sync-pembayaran.start') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.status) {
                    addLog('▶ Sinkronisasi dimulai');
                    updateStatus(true);
                    startPolling();
                } else {
                    alert('Error: ' + data.message);
                    addLog(`✗ Error: ${data.message}`);
                }
            } catch (error) {
                alert('Error: ' + error.message);
                addLog(`✗ Error: ${error.message}`);
            }
        });

        // ===== CANCEL SYNC =====
        document.getElementById('cancelBtn').addEventListener('click', async function() {
            if (!isRunning) return;

            if (!confirm('Yakin ingin membatalkan sinkronisasi?')) return;

            try {
                const response = await fetch('{{ route('admin.sync-pembayaran.cancel') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.status) {
                    addLog('⏹ Sinkronisasi dibatalkan');
                    updateStatus(false);
                    stopPolling();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // ===== RESET PROGRESS =====
        document.getElementById('resetBtn').addEventListener('click', async function() {
            if (isRunning) {
                alert('Tidak bisa reset saat proses sedang berjalan');
                return;
            }

            if (!confirm('Reset semua progress?')) return;

            try {
                const response = await fetch('{{ route('admin.sync-pembayaran.reset') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.status) {
                    addLog('🔄 Progress di-reset');
                    // Reset UI
                    document.getElementById('progressBar').style.width = '0%';
                    document.getElementById('progressPercent').textContent = '0%';
                    document.getElementById('totalCount').textContent = '0';
                    document.getElementById('processedCount').textContent = '0';
                    document.getElementById('successCount').textContent = '0';
                    document.getElementById('failedCount').textContent = '0';
                    document.getElementById('syncStatus').textContent = 'Menunggu...';
                    startTime = null;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // ===== POLLING =====
        function startPolling() {
            pollingInterval = setInterval(() => {
                fetchProgress();
            }, 500);
            addLog('📡 Polling dimulai');
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                addLog('📡 Polling dihentikan');
            }
        }

        // ===== INITIAL LOAD =====
        document.addEventListener('DOMContentLoaded', function() {
            fetchProgress();
            if (isRunning) {
                startTime = Date.now();
                startPolling();
                addLog('📡 Polling dilanjutkan (proses sedang berjalan)');
            }
        });

        // ===== CLEANUP =====
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });
    </script>
@endsection
