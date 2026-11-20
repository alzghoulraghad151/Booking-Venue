@if ($paginator->hasPages())
    <nav class="admin-pagination" aria-label="Pagination">
        <p>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</p>
        <div class="admin-pagination-links">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-page-number" aria-hidden="true">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="admin-page-number" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="admin-page-number" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span aria-disabled="true">Next</span>
            @endif
        </div>
    </nav>
@endif
