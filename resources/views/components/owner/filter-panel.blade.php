@props(['formId', 'action', 'description' => null])

<section class="dashboard-panel owner-filter-panel" @if ($description) aria-labelledby="{{ $formId }}-title" @else aria-label="Filters" @endif>
    @if ($description)
        <header class="dashboard-panel-header owner-filter-header">
            <div>
                <h2 id="{{ $formId }}-title">Search and filters</h2>
                <p class="dashboard-note">{{ $description }}</p>
            </div>
        </header>
    @endif
    <form id="{{ $formId }}" class="owner-filter-form{{ $description ? '' : ' owner-filter-form--compact' }}" action="{{ $action }}" method="GET">
        {{ $slot }}
        <div class="owner-filter-actions">
            <button class="admin-button admin-filter-submit" type="submit">Apply filters</button>
            <a class="admin-filter-reset" href="{{ $action }}">Reset</a>
        </div>
    </form>
</section>
