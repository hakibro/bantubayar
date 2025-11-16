@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container my-4">
        <h3>Sinkronisasi Data Siswa</h3>

        <button id="btnSync" class="btn btn-primary mt-3">Mulai Sinkronisasi</button>

        <div id="progressBox" class="mt-4" style="display:none;">
            <h5>Proses Sinkronisasi...</h5>
            <div class="progress">
                <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
            </div>

            <p class="mt-2" id="progressText"></p>
        </div>
    </div>

    <script>
        document.getElementById('btnSync').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;

            document.getElementById('progressBox').style.display = 'block';
            document.getElementById('progressText').innerHTML = "Mengambil data...";

            fetch("/admin/siswa/sync-all/run")
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        document.getElementById('progressBar').style.width = "100%";
                        document.getElementById('progressBar').innerHTML = "100%";
                        document.getElementById('progressText').innerHTML =
                            "Sinkronisasi selesai. Total: " + res.total + " siswa.";
                    } else {
                        document.getElementById('progressText').innerHTML =
                            "Gagal: " + res.message;
                    }
                });
        });
    </script>
@endsection
