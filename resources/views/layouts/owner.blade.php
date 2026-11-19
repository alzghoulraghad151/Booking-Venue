<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Owner') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/convene-admin.css') }}">
</head>
<body class="convene-admin">
    <a class="admin-skip" href="#owner-content">Skip to content</a>

    @include('owner.partials.sidebar')

    <main id="owner-content" class="admin-content" tabindex="-1">
        <h1>@yield('title', 'Owner workspace')</h1>
        @yield('content')
    </main>
</body>
</html>
