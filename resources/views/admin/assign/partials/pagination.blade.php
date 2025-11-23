@if ($paginator->hasPages())
    <div class="mt-4 flex justify-center">
        <nav class="inline-flex space-x-1">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1 bg-gray-200 text-gray-400 rounded">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="ajaxPage px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">‹</a>
            @endif

            {{-- Page Numbers --}}
            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp

            {{-- Always show page 1 --}}
            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" class="ajaxPage px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">1</a>

                @if ($start > 2)
                    <span class="px-3 py-1 text-gray-400">...</span>
                @endif
            @endif

            {{-- Dynamic middle pages --}}
            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $current)
                    <span class="px-3 py-1 bg-blue-600 text-white rounded">{{ $i }}</span>
                @else
                    <a href="{{ $paginator->url($i) }}"
                        class="ajaxPage px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">{{ $i }}</a>
                @endif
            @endfor

            {{-- Always show last page --}}
            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="px-3 py-1 text-gray-400">...</span>
                @endif

                <a href="{{ $paginator->url($last) }}"
                    class="ajaxPage px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">{{ $last }}</a>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="ajaxPage px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">›</a>
            @else
                <span class="px-3 py-1 bg-gray-200 text-gray-400 rounded">›</span>
            @endif

        </nav>
    </div>
@endif
