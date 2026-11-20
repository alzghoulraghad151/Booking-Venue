<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password | Convene</title>

    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="{{ asset('js/auth-submit.js') }}" defer></script>
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>Reset password</h1>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $email) }}" autocomplete="email" required>
                @error('email')
                    <p role="alert">{{ $message }}</p>
                @enderror
                @error('token')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">New password</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password" minlength="8" maxlength="64"
                       aria-describedby="password-help" required>
                <p id="password-help">Use at least 8 characters, with an uppercase letter, a lowercase letter, and a symbol.</p>
                @error('password')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" autocomplete="new-password" required>
            </div>

            <button type="submit" class="auth-submit" data-pending-label="Updating…">
                Reset password
            </button>
        </form>

        <p><a href="{{ route('password.request') }}">Request a new reset link</a></p>
        <a href="{{ route('login') }}">Back to sign in</a>
    </main>
</body>
</html>