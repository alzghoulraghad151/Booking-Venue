<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/convene-admin.css') }}">
    @stack('styles')
</head>
<body class="convene-admin">
    <a class="admin-skip" href="#admin-content">Skip to content</a>

    @section('sidebar')
        @include('admin.partials.sidebar')
    @show

    <main id="admin-content" class="admin-content" tabindex="-1">
        @sectionMissing('hide-layout-heading')
            <h1>@yield('title', 'Administration')</h1>
        @endif
        @yield('content')
    </main>
</body>
</html>

