@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Pilih Siswa untuk Home Visit</h1>
        </div>

        <!-- Filter Card -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" action="{{ route('admin.home-visit.select') }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama/ID</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full border rounded-lg px-3 py-2" placeholder="Nama atau ID Person">
                    </div>

                    <!-- Lembaga -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lembaga</label>
                        <select name="lembaga" id="filterLembaga" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Semua Lembaga</option>
                            @foreach ($daftarLembaga as $lembaga)
                                <option value="{{ $lembaga }}" {{ request('lembaga') == $lembaga ? 'selected' : '' }}>
                                    {{ $lembaga }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select name="kelas" id="filterKelas" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Semua Kelas</option>
                            <!-- Diisi via AJAX -->
                        </select>
                    </div>

                    <!-- Asrama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Asrama</label>
                        <select name="asrama" id="filterAsrama" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Semua Asrama</option>
                            @foreach ($daftarAsrama as $asrama)
                                <option value="{{ $asrama }}" {{ request('asrama') == $asrama ? 'selected' : '' }}>
                                    {{ $asrama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kamar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kamar</label>
                        <select name="kamar" id="filterKamar" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Semua Kamar</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Siswa -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Person</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lembaga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asrama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="siswaTableBody">
                        @include('admin.home-visit.partials.table', ['siswa' => $siswa])
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t">
                {{ $siswa->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Fungsi untuk memuat kelas berdasarkan lembaga
            function loadKelas(lembaga, selectedKelas = '') {
                if (!lembaga) {
                    $('#filterKelas').html('<option value="">Semua Kelas</option>');
                    return;
                }

                $.get('{{ route('admin.home-visit.kelas') }}', {
                    lembaga: lembaga
                }, function(data) {
                    let options = '<option value="">Semua Kelas</option>';
                    data.forEach(kelas => {
                        options +=
                            `<option value="${kelas}" ${kelas == selectedKelas ? 'selected' : ''}>${kelas}</option>`;
                    });
                    $('#filterKelas').html(options);
                });
            }

            // Fungsi untuk memuat kamar berdasarkan asrama
            function loadKamar(asrama, selectedKamar = '') {
                if (!asrama) {
                    $('#filterKamar').html('<option value="">Semua Kamar</option>');
                    return;
                }

                $.get('{{ route('admin.home-visit.kamar') }}', {
                    asrama: asrama
                }, function(data) {
                    let options = '<option value="">Semua Kamar</option>';
                    data.forEach(kamar => {
                        options +=
                            `<option value="${kamar}" ${kamar == selectedKamar ? 'selected' : ''}>${kamar}</option>`;
                    });
                    $('#filterKamar').html(options);
                });
            }

            $(document).ready(function() {
                // Load kelas jika lembaga sudah dipilih
                const lembaga = $('#filterLembaga').val();
                if (lembaga) {
                    loadKelas(lembaga, '{{ request('kelas') }}');
                }

                // Load kamar jika asrama sudah dipilih
                const asrama = $('#filterAsrama').val();
                if (asrama) {
                    loadKamar(asrama, '{{ request('kamar') }}');
                }

                // Event change
                $('#filterLembaga').change(function() {
                    loadKelas($(this).val());
                    $('#filterKelas').val('');
                });

                $('#filterAsrama').change(function() {
                    loadKamar($(this).val());
                    $('#filterKamar').val('');
                });

                // Submit filter via AJAX untuk update tabel tanpa reload
                $('#filterForm').submit(function(e) {
                    e.preventDefault();
                    $.get($(this).attr('action'), $(this).serialize(), function(data) {
                        $('#siswaTableBody').html(data);
                        // Update pagination jika perlu (sederhana, kita reload page saja untuk pagination)
                    }).fail(function() {
                        location.reload();
                    });
                });
            });
        </script>
    @endpush
@endsection
