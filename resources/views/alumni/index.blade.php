@extends('layouts.dashboard')

@section('title', 'Alumni')

@section('content')
    <div class="bg-gray-100 p-6 rounded-xl shadow">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Alumni</h1>
                    <p class="text-sm text-gray-500">Alumni yang masih memiliki tunggakan pembayaran.</p>
                </div>
            </div>

            <form id="filterForm" class="mb-4">
                <div class="max-w-7xl mx-auto bg-white border border-blue-100 rounded-2xl shadow-xl shadow-blue-900/5 overflow-hidden">
                    <div class="p-4 flex flex-wrap items-center gap-2">
                        <input type="text" name="search" placeholder="Cari nama atau ID Yayasan..."
                            value="{{ request('search') }}"
                            class="flex-grow min-w-[200px] px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-blue-500 transition-all">

                        <select name="tahun_lulus"
                            class="shrink-0 h-11 px-3 bg-slate-50 border border-gray-200 rounded-xl text-sm text-gray-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
                            <option value="">Semua Tahun Lulus</option>
                            @foreach ($tahunLulusOptions as $thn)
                                <option value="{{ $thn }}" {{ request('tahun_lulus') == $thn ? 'selected' : '' }}>
                                    {{ $thn }}</option>
                            @endforeach
                        </select>

                        <select name="range_tunggakan"
                            class="shrink-0 h-11 px-3 bg-slate-50 border border-gray-200 rounded-xl text-sm text-gray-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
                            <option value="">Semua Tunggakan</option>
                            <option value="0" {{ request('range_tunggakan') === '0' ? 'selected' : '' }}>Rp 0</option>
                            <option value="1_500k" {{ request('range_tunggakan') === '1_500k' ? 'selected' : '' }}>Rp 1 - 500rb</option>
                            <option value="500k_1jt" {{ request('range_tunggakan') === '500k_1jt' ? 'selected' : '' }}>Rp 500rb - 1jt</option>
                            <option value="1jt_2jt" {{ request('range_tunggakan') === '1jt_2jt' ? 'selected' : '' }}>Rp 1jt - 2jt</option>
                            <option value="2jt_plus" {{ request('range_tunggakan') === '2jt_plus' ? 'selected' : '' }}>Lebih dari Rp 2jt</option>
                        </select>

                        <select name="sort"
                            class="shrink-0 h-11 px-3 bg-slate-50 border border-gray-200 rounded-xl text-sm text-gray-700 outline-none focus:border-blue-500 transition-all cursor-pointer">
                            <option value="">Urutkan: Nama</option>
                            <option value="tunggakan_desc" {{ request('sort') === 'tunggakan_desc' ? 'selected' : '' }}>Tunggakan Terbesar</option>
                            <option value="tunggakan_asc" {{ request('sort') === 'tunggakan_asc' ? 'selected' : '' }}>Tunggakan Terkecil</option>
                        </select>

                        <button type="button" id="resetButton"
                            class="h-11 px-4 text-gray-400 border border-gray-200 rounded-xl transition-all">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </form>

            <div id="tableContainer" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mt-6">
                @include('alumni.partials.list-alumni')
            </div>

            <div class="mt-8" id="paginationContainer">
                {{ $alumni->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function fetchContent() {
                const formData = $('#filterForm').serialize();
                $('#tableContainer').css('opacity', '0.5');

                $.ajax({
                    url: window.location.pathname,
                    data: formData,
                    success: function(response) {
                        $('#tableContainer').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#tableContainer').css('opacity', '1');
                    }
                });
            }

            $('#filterForm input, #filterForm select').on('change input', function() {
                fetchContent();
            });

            $('#resetButton').on('click', function() {
                $('#filterForm input[name="search"]').val('');
                $('#filterForm select[name="tahun_lulus"]').val('');
                $('#filterForm select[name="range_tunggakan"]').val('');
                $('#filterForm select[name="sort"]').val('');
                fetchContent();
            });
        });
    </script>
@endpush
