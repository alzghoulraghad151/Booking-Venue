@props(['title', 'description' => null, 'actionUrl' => null, 'actionLabel' => null])

<header class="admin-list-heading">
    <div>
        <h1>{{ $title }}</h1>
        @if ($description)
            <p class="admin-description">{{ $description }}</p>
        @endif
    </div>
    @if ($actionUrl && $actionLabel)
        <a class="admin-list-create admin-button admin-button--primary admin-button--page" href="{{ $actionUrl }}">
            <svg aria-hidden="true" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M8 3v10M3 8h10"/></svg> {{ $actionLabel }}
        </a>
    @endif
</header>
