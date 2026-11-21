@extends('layouts.auth')

@section('title', "Sign in")

@section('auth-form')
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
@endsection

@push('scripts')
    <script src="{{ asset('js/auth-submit.js') }}" defer></script>
    <script src="{{ asset('js/login.js') }}" defer></script>
@endpush
