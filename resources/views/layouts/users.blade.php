<!doctype html>
<html lang="@yield('language', 'en')" dir="@yield('direction', 'ltr')">
<head>
    @include('partials.site.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Find event spaces') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-dar-chrome.css') }}">
    @stack('styles')
    @if (request()->routeIs('account.personal-details.edit', 'account.favourites.index', 'account.settings'))
        <link rel="stylesheet" href="{{ asset('css/convene-my-bookings-dar.css') }}">
    @endif
    <script src="{{ asset('js/convene-site-navigation.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/convene-toasts.css') }}">
</head>
<body class="convene-site">
    @php($brandRoute = Route::has('home') ? 'home' : 'spaces.search')

    <a class="site-skip-link" href="#site-content">Skip to content</a>

    @section('navbar')
        @include('partials.site.navbar', ['brandUrl' => route($brandRoute)])
    @show

    <main class="site-main" id="site-content" tabindex="-1">
        @yield('content')
    </main>

    @section('footer')
        @include('partials.site.footer', ['brandUrl' => route($brandRoute)])
    @show

    @stack('scripts')
    @include('partials.site.toasts')
</body>
</html>
