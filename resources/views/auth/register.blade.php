@extends('layouts.users')

@section('title', "Create an account")

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
    <div class="auth-page">
<div class="auth-card">
        <h1>Create an account</h1>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div>
                <label for="name">Name</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name') }}" autocomplete="name" required>

                @error('name')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" autocomplete="email" required>

                @error('email')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
    <label for="phone">Phone number</label>
    <input id="phone" name="phone" type="tel"
           value="{{ old('phone') }}" autocomplete="tel"
           maxlength="20" required>

    @error('phone')
        <p role="alert">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="password">Password</label>
    <input id="password" name="password" type="password"
           autocomplete="new-password" minlength="8" maxlength="64"
           aria-describedby="password-rules" required>

    <ul id="password-rules" class="password-rules">
        <li data-rule="length">At least 8 characters</li>
        <li data-rule="uppercase">At least one uppercase letter</li>
        <li data-rule="lowercase">At least one lowercase letter</li>
        <li data-rule="symbol">At least one symbol, such as !, @, or #</li>
    </ul>

    @error('password')
        <p role="alert">{{ $message }}</p>
    @enderror
</div>

            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" autocomplete="new-password" required>

                @error('password_confirmation')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

           <button type="submit" class="auth-submit" data-pending-label="Creating account…">
    Create account
</button>
        </form>

        <a href="{{ route('login') }}">Back to sign in</a>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth-submit.js') }}" defer></script>
    <script src="{{ asset('js/register.js') }}" defer></script>
@endpush
