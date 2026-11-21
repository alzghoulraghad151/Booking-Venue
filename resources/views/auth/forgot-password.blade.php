@extends('layouts.auth')

@section('title', "Forgot password")

@section('auth-form')
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
@endsection

@push('scripts')
    <script src="{{ asset('js/auth-submit.js') }}" defer></script>
@endpush
