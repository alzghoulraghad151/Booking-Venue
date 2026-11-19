<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account</title>
</head>
<body>
    <h1>Create an account</h1>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div>
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name') }}"
                   autocomplete="name" required>
            @error('name')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   autocomplete="email" required>
            @error('email')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password"
                   autocomplete="new-password" required>
            @error('password')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation"
                   type="password" autocomplete="new-password" required>
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>