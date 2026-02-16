@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-custom justify-content-end mb-0">
            {{-- Previous Page Link --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous" {{ $paginator->onFirstPage() ? 'tabindex="-1"' : '' }}>
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $showEllipsis = $lastPage > 5;
            @endphp

            @if ($showEllipsis)
                @if ($currentPage <= 4)
                    {{-- Near start: show 1-5, ellipsis, last page --}}
                    @for ($i = 1; $i <= min(5, $lastPage); $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @elseif ($currentPage >= $lastPage - 3)
                    {{-- Near end: show first page, ellipsis, last 5 --}}
                    <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    @for ($i = max($lastPage - 4, 1); $i <= $lastPage; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                @else
                    {{-- Middle: show first page, ellipsis, current-1 to current+1, ellipsis, last page --}}
                    <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    @for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @endif
            @else
                {{-- 5 or fewer pages: show all --}}
                @for ($i = 1; $i <= $lastPage; $i++)
                    <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
            @endif

            {{-- Next Page Link --}}
            <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next" {{ !$paginator->hasMorePages() ? 'tabindex="-1"' : '' }}>
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
@endif
