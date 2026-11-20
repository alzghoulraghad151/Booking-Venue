<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | Convene</title>

    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="{{ asset('js/auth-submit.js') }}" defer></script>
    <script src="{{ asset('js/login.js') }}" defer></script>
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>Sign in</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" autocomplete="username" required>

                @error('email')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">Password</label>

                <div class="password-field">
                    <input id="password" name="password" type="password"
                           autocomplete="current-password" required>

                    <button type="button" id="toggle-password" class="password-eye"
                            aria-controls="password" aria-label="Show password" hidden>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="2.5"/>
                            <path id="password-eye-slash" d="M3 3l18 18"/>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

          <button type="submit" class="auth-submit" data-pending-label="Signing in…">
    Sign in
</button>
        </form>
<p><a href="{{ route('password.request') }}">Forgot password?</a></p>
        <a href="{{ route('register') }}">Create an account</a>
    </main>
</body>
</html>