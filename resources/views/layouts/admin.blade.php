<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/convene-admin.css') }}">
</head>
<body class="convene-admin">
    <a class="admin-skip" href="#admin-content">Skip to content</a>

    @include('admin.partials.sidebar')

    <main id="admin-content" class="admin-content" tabindex="-1">
        <h1>@yield('title', 'Administration')</h1>
        @yield('content')
    </main>
</body>
</html>

