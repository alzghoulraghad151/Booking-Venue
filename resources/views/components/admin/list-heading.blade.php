@props(['title', 'actionUrl' => null, 'actionLabel' => null])

<header class="admin-list-heading">
    <h1>{{ $title }}</h1>
    @if ($actionUrl && $actionLabel)
        <a class="admin-list-create" href="{{ $actionUrl }}">
            <span aria-hidden="true">+</span> {{ $actionLabel }}
        </a>
    @endif
</header>
