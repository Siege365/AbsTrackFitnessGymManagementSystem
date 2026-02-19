@if ($paginator->hasPages())
    <div class="pagination-wrapper">
        {{-- Info Text (Left) --}}
        <div class="pagination-info">
            @if ($paginator->firstItem() && $paginator->lastItem())
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} entries
            @else
                Showing 0 to 0 of 0 entries
            @endif
        </div>

        {{-- Navigation Controls (Right) --}}
        <div class="pagination-controls">
            {{-- First Page Button --}}
            <a href="{{ $paginator->url(1) }}" 
               class="pagination-btn icon-btn {{ $paginator->onFirstPage() ? 'disabled' : '' }}"
               aria-label="First"
               {{ $paginator->onFirstPage() ? 'tabindex="-1"' : '' }}>
                <i class="mdi mdi-chevron-double-left"></i>
            </a>

            {{-- Previous Page Button --}}
            <a href="{{ $paginator->previousPageUrl() }}" 
               class="pagination-btn icon-btn {{ $paginator->onFirstPage() ? 'disabled' : '' }}"
               aria-label="Previous"
               {{ $paginator->onFirstPage() ? 'tabindex="-1"' : '' }}>
                <i class="mdi mdi-chevron-left"></i>
            </a>

            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                
                // Calculate visible page range (current ± 2)
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
                
                // Adjust if we have fewer than 5 pages visible
                if ($endPage - $startPage < 4) {
                    if ($startPage == 1) {
                        $endPage = min($lastPage, $startPage + 4);
                    } else {
                        $startPage = max(1, $endPage - 4);
                    }
                }
            @endphp

            {{-- First page if not in range --}}
            @if ($startPage > 1)
                <a href="{{ $paginator->url(1) }}" class="pagination-btn">1</a>
                @if ($startPage > 2)
                    <span class="pagination-ellipsis">...</span>
                @endif
            @endif

            {{-- Page Numbers --}}
            @for ($i = $startPage; $i <= $endPage; $i++)
                <a href="{{ $paginator->url($i) }}" 
                   class="pagination-btn {{ $currentPage == $i ? 'active' : '' }}">
                    {{ $i }}
                </a>
            @endfor

            {{-- Last page if not in range --}}
            @if ($endPage < $lastPage)
                @if ($endPage < $lastPage - 1)
                    <span class="pagination-ellipsis">...</span>
                @endif
                <a href="{{ $paginator->url($lastPage) }}" class="pagination-btn">{{ $lastPage }}</a>
            @endif

            {{-- Next Page Button --}}
            <a href="{{ $paginator->nextPageUrl() }}" 
               class="pagination-btn icon-btn {{ !$paginator->hasMorePages() ? 'disabled' : '' }}"
               aria-label="Next"
               {{ !$paginator->hasMorePages() ? 'tabindex="-1"' : '' }}>
                <i class="mdi mdi-chevron-right"></i>
            </a>

            {{-- Last Page Button --}}
            <a href="{{ $paginator->url($lastPage) }}" 
               class="pagination-btn icon-btn {{ !$paginator->hasMorePages() ? 'disabled' : '' }}"
               aria-label="Last"
               {{ !$paginator->hasMorePages() ? 'tabindex="-1"' : '' }}>
                <i class="mdi mdi-chevron-double-right"></i>
            </a>
        </div>
    </div>
@endif
