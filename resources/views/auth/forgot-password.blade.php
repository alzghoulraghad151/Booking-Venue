<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password | Convene</title>

    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="{{ asset('js/auth-submit.js') }}" defer></script>
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>Forgot password</h1>
        <p>Enter your email to request a password reset link.</p>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" autocomplete="email"
                       maxlength="255" required>

                @error('email')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-submit" data-pending-label="Sending…">
                Send reset link
            </button>
        </form>

        <a href="{{ route('login') }}">Back to sign in</a>
    </main>
</body>
</html>