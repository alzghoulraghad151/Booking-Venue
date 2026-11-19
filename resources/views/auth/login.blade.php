<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
</head>
<body>
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
            <input id="password" name="password" type="password"
                   autocomplete="current-password" required>
        </div>

        <button type="submit">Sign in</button>
    </form>

    <a href="{{ route('register') }}">Create an account</a>
</body>
</html>