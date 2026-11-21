@if ($paginator->hasPages())
    <nav class="places-pagination" aria-label="{{ $label }}">
        <span class="places-pagination__summary">Page {{ $paginator->currentPage() }} of {{ max(1, $paginator->lastPage()) }}</span>
        <div class="places-pagination__pages">
        @if ($paginator->onFirstPage())
            <span class="places-pagination__disabled places-pagination__previous" aria-disabled="true">← Previous</span>
        @else
            <a class="places-pagination__previous" href="{{ $paginator->previousPageUrl() }}">← Previous</a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            @if ($page === $paginator->currentPage())
                <span class="is-current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="places-pagination__next" href="{{ $paginator->nextPageUrl() }}">Next →</a>
        @else
            <span class="places-pagination__disabled places-pagination__next" aria-disabled="true">Next →</span>
        @endif
        </div>
    </nav>
@endif
