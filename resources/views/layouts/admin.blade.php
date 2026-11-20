@php($adminActions = request()->routeIs('admin.dashboard', 'admin.users.*', 'admin.venues.*', 'admin.event-types.*', 'admin.reviews.*'))
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/convene-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-admin-lists.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-admin-ui.css') }}">
    <script src="{{ asset('js/convene-admin-navigation.js') }}" defer></script>
    @if ($adminActions)
        <link rel="stylesheet" href="{{ asset('css/convene-admin-actions.css') }}">
        <script src="{{ asset('js/convene-admin-actions.js') }}" defer></script>
    @endif
</head>
<body class="convene-admin convene-admin-ui{{ request()->routeIs('admin.dashboard', 'admin.users.*', 'admin.venues.*', 'admin.event-types.*', 'admin.reviews.*', 'admin.bookings.*') ? ' admin-warm-actions' : '' }}">
    <a class="admin-skip" href="#admin-content">Skip to content</a>

    @section('sidebar')
        @include('admin.partials.sidebar')
    @show

    <dialog class="admin-nav-drawer" id="admin-nav-drawer" aria-label="Administration navigation">
        <button class="admin-nav-close" type="button" autofocus>Close menu</button>
    </dialog>

    <main id="admin-content" class="admin-content{{ request()->routeIs('admin.venues.index', 'admin.users.index', 'admin.event-types.index', 'admin.reviews.index') ? ' admin-management-list' : '' }}" tabindex="-1">
        <button class="admin-nav-toggle" type="button" aria-haspopup="dialog" aria-controls="admin-nav-drawer" hidden>
            <span aria-hidden="true">☰</span> Menu
        </button>
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
    </main>
</body>
</html>
