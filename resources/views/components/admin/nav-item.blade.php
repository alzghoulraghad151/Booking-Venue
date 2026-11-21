@props(['label', 'routeName', 'activePattern'])

@if (Route::has($routeName))
    <a {{ $attributes->merge(['class' => 'admin-nav-item']) }} href="{{ route($routeName) }}"
        @if (request()->routeIs($activePattern)) aria-current="page" @endif>
        {{ $label }}
    </a>
@else
    <span class="admin-nav-item admin-nav-item--unavailable">
        {{ $label }} <small>Coming soon</small>
    </span>
@endif
