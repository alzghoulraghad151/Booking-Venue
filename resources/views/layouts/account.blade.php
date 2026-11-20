<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
</head>
<body class="account-page">
    <div class="account-layout">
        <nav class="account-nav" aria-label="Account">
            <a href="{{ route('account.personal-details.edit') }}"
               @if (request()->routeIs('account.personal-details.edit')) aria-current="page" @endif>
                Personal Details
            </a>

            <a href="{{ route('account.settings') }}"
               @if (request()->routeIs('account.settings')) aria-current="page" @endif>
                Account Settings
            </a>
        </nav>

        <main class="account-content @yield('content-class')">
            @yield('content')
        </main>
    </div>
</body>
</html>