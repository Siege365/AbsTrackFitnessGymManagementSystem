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
                $showEllipsis = $lastPage > 7;
            @endphp

            @if ($showEllipsis)
                {{-- Show: 1 2 3 4 5 ... lastPage-1 lastPage --}}
                @if ($currentPage <= 4)
                    {{-- Near start: show 1-5, ellipsis, last 2 --}}
                    @for ($i = 1; $i <= 5; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    @for ($i = $lastPage - 1; $i <= $lastPage; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                @elseif ($currentPage >= $lastPage - 3)
                    {{-- Near end: show first 2, ellipsis, last 5 --}}
                    @for ($i = 1; $i <= 2; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    @for ($i = $lastPage - 4; $i <= $lastPage; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                @else
                    {{-- Middle: show first 2, ellipsis, current-1 to current+1, ellipsis, last 2 --}}
                    @for ($i = 1; $i <= 2; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
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
                    @for ($i = $lastPage - 1; $i <= $lastPage; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                @endif
            @else
                {{-- 7 or fewer pages: show all --}}
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
