@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        
        $start = max($currentPage - 2, 1);
        $end = min($start + 4, $lastPage);
        if ($end - $start < 4) {
            $start = max($end - 4, 1);
        }
    @endphp

    <div class="pagination-container" style="display: flex; align-items: center; justify-content: center; margin-top: 24px;">
        <div class="pagination" style="margin: 0; display: flex; align-items: center; gap: 6px;">
            {{-- First Page Link --}}
            @if ($currentPage == 1)
                <span class="disabled" style="opacity: 0.5; cursor: not-allowed;" title="Halaman Pertama">«</span>
            @else
                <a href="{{ $paginator->url(1) }}" title="Halaman Pertama">«</a>
            @endif

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="disabled" style="opacity: 0.5; cursor: not-allowed;" title="Halaman Sebelumnya">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" title="Halaman Sebelumnya">‹</a>
            @endif

            {{-- Page Numbers --}}
            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $currentPage)
                    {{-- Active Page as an Editable Input for Manual Page Jump --}}
                    <input type="number" class="active-page-input" value="{{ $currentPage }}" min="1" max="{{ $lastPage }}"
                           style="width: 36px; padding: 7px 0; text-align: center; border: 1px solid var(--teal); background: var(--teal); color: #fff; font-size: 13px; font-weight: 700; border-radius: var(--radius-sm); font-family: 'DM Sans', sans-serif; cursor: text; box-sizing: border-box; -webkit-appearance: none; -moz-appearance: textfield; margin: 0; outline: none; vertical-align: middle;"
                           onkeydown="if(event.key==='Enter') goToManualPage(this, {{ $lastPage }})"
                           title="Ketik halaman lalu tekan Enter untuk melompat">
                @else
                    <a href="{{ $paginator->url($i) }}" class="{{ abs($i - $currentPage) > 1 ? 'secondary-page' : '' }}">{{ $i }}</a>
                @endif
            @endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" title="Halaman Selanjutnya">›</a>
            @else
                <span class="disabled" style="opacity: 0.5; cursor: not-allowed;" title="Halaman Selanjutnya">›</span>
            @endif

            {{-- Last Page Link --}}
            @if ($currentPage == $lastPage)
                <span class="disabled" style="opacity: 0.5; cursor: not-allowed;" title="Halaman Terakhir">»</span>
            @else
                <a href="{{ $paginator->url($lastPage) }}" title="Halaman Terakhir">»</a>
            @endif
        </div>
    </div>

    <style>
        /* Hide number input spinners globally for the active page input */
        .active-page-input::-webkit-outer-spin-button,
        .active-page-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hide outer secondary pages on mobile screen widths to show maximum 3 pages */
        @media (max-width: 576px) {
            .secondary-page {
                display: none !important;
            }
        }
    </style>

    <script>
        if (typeof goToManualPage !== 'function') {
            function goToManualPage(input, lastPage) {
                let page = parseInt(input.value);
                if (isNaN(page) || page < 1) page = 1;
                if (page > lastPage) page = lastPage;
                
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('page', page);
                window.location.search = urlParams.toString();
            }
        }
    </script>
@endif
