@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination">
    <ul style="display:flex; align-items:center; gap:5px; list-style:none; padding:0; margin:0;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li>
                <span class="pag-btn pag-nav pag-disabled" aria-disabled="true">
                    <i class="fas fa-chevron-left" style="font-size:11px;"></i>
                    Prev
                </span>
            </li>
        @else
            <li>
                <a href="{{ $paginator->previousPageUrl() }}" class="pag-btn pag-nav" rel="prev">
                    <i class="fas fa-chevron-left" style="font-size:11px;"></i>
                    Prev
                </a>
            </li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li>
                    <span class="pag-btn pag-ellipsis">&hellip;</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li>
                            <span class="pag-btn pag-active" aria-current="page">{{ $page }}</span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $url }}" class="pag-btn">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->nextPageUrl() }}" class="pag-btn pag-nav" rel="next">
                    Next
                    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
                </a>
            </li>
        @else
            <li>
                <span class="pag-btn pag-nav pag-disabled" aria-disabled="true">
                    Next
                    <i class="fas fa-chevron-right" style="font-size:11px;"></i>
                </span>
            </li>
        @endif

    </ul>
</nav>
@endif