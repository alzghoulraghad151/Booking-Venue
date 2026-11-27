@php
    $adminActions = request()->routeIs('admin.dashboard', 'admin.users.*', 'admin.venues.*', 'admin.event-types.*', 'admin.reviews.*', 'admin.profile.*', 'owner.*');
    $editReturnRoute = [
        'admin.users.create' => 'admin.users.index',
        'admin.event-types.create' => 'admin.event-types.index',
        'owner.venues.create' => 'owner.venues.index',
        'owner.spaces.create' => 'owner.spaces.index',
        'owner.space-layouts.create' => 'owner.space-layouts.index',
        'owner.packages.create' => 'owner.packages.index',

        'admin.users.edit' => 'admin.users.index',
        'admin.event-types.edit' => 'admin.event-types.index',
        'owner.venues.edit' => 'owner.venues.index',
        'owner.spaces.edit' => 'owner.spaces.index',
        'owner.space-layouts.edit' => 'owner.space-layouts.index',
        'owner.packages.edit' => 'owner.packages.index',
    ][request()->route()?->getName()] ?? null;
@endphp
<!doctype html>
<html lang="en">
<head>
    @include('partials.site.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/convene-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-admin-lists.css') }}">
    @if (request()->routeIs('owner.*'))
        <link rel="stylesheet" href="{{ asset('css/convene-owner.css') }}">
    @endif
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-admin-ui.css') }}">
    <script src="{{ asset('js/convene-admin-navigation.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/convene-management-dialogs.css') }}">
    <script src="{{ asset('js/convene-management-dialogs.js') }}" defer></script>
    @if ($adminActions)
        <link rel="stylesheet" href="{{ asset('css/convene-admin-actions.css') }}">
        <script src="{{ asset('js/convene-admin-actions.js') }}" defer></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/convene-toasts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-management-dar.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;500;700;800&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Unbounded:wght@200;300;500;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=Amiri:wght@400;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/convene-navy.css') }}?v={{ @filemtime(public_path('css/convene-navy.css')) }}">
</head>
<body class="convene-admin convene-admin-ui convene-dar-management{{ request()->routeIs('admin.dashboard', 'admin.users.*', 'admin.venues.*', 'admin.event-types.*', 'admin.reviews.*', 'admin.profile.*', 'admin.bookings.*', 'owner.*') ? ' admin-warm-actions' : '' }}">
    <a class="admin-skip" href="#admin-content">Skip to content</a>

    @section('sidebar')
        @include('admin.partials.sidebar')
    @show

    <dialog class="admin-nav-drawer" id="admin-nav-drawer" aria-label="Administration navigation">
        <button class="admin-nav-close" type="button" autofocus>Close menu</button>
    </dialog>

    <main id="admin-content" class="admin-content{{ request()->routeIs('admin.venues.index', 'admin.users.index', 'admin.event-types.index', 'admin.reviews.index', 'owner.venues.index', 'owner.spaces.index', 'owner.space-layouts.index', 'owner.packages.index') ? ' admin-management-list' : '' }}" tabindex="-1">
        <button class="admin-nav-toggle" type="button" aria-haspopup="dialog" aria-controls="admin-nav-drawer" hidden>
            <span aria-hidden="true">☰</span> Menu
        </button>
        @if ($editReturnRoute)
            <section data-edit-page @if (request()->routeIs('*.create')) data-create-form @endif data-title="@yield('title')" data-return-url="{{ route($editReturnRoute) }}">
        @endif
        @sectionMissing('hide-layout-heading')
            @hasSection('heading')
                <h1>@yield('heading')</h1>
            @else
                <h1>@yield('title', 'Administration')</h1>
            @endif
            @hasSection('subtitle')
                <p class="{{ $adminActions ? 'admin-description admin-description--page' : 'admin-page-subtitle' }}">@yield('subtitle')</p>
            @endif
        @endif
        @yield('content')
        @if ($editReturnRoute)
            </section>
        @endif
        @include('admin.partials.management-dialogs')
    </main>
    @include('partials.site.toasts')
</body>
</html>
