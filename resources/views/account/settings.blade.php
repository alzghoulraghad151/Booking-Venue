@extends('layouts.account')

@section('title', 'Account Settings')
@section('content-class', 'account-settings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account-settings.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/account-settings.js') }}" defer></script>
@endpush

@section('account-content')
    <header class="settings-heading">
        <h1>Account Settings</h1>
    </header>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <section class="settings-card" aria-labelledby="change-password-heading">
        <h2 id="change-password-heading">Change Password</h2>

        <button type="button" id="open-password-dialog" aria-haspopup="dialog">
            Change password
        </button>
    </section>

    @if (auth()->user()->role === 'organizer')
        <section class="settings-card" aria-labelledby="delete-account-heading">
            <h2 id="delete-account-heading">Delete Account</h2>
            <p>Enter your current password to confirm. You’ll be signed out immediately.</p>

            @error('account', 'deleteAccount')
                <p role="alert">{{ $message }}</p>
            @enderror

            <form method="POST" action="{{ route('account.destroy') }}">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_current_password">Current password</label>
                    <input id="delete_current_password" name="current_password"
                           type="password" autocomplete="current-password" required>

                    @error('current_password', 'deleteAccount')
                        <p role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit">Delete account</button>
            </form>
        </section>
    @endif

    <dialog id="password-dialog" aria-labelledby="password-dialog-title"
            @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
                data-show-errors
            @endif>
        <h2 id="password-dialog-title">Change Password</h2>

        <form method="POST" action="{{ route('account.password.update') }}">
            @csrf
            @method('PATCH')

            <div>
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password"
                       autocomplete="current-password" required autofocus>

                @error('current_password')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">New password</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password" required>

                @error('password')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" autocomplete="new-password" required>

                @error('password_confirmation')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="settings-dialog-actions">
                <button type="button" id="close-password-dialog">Cancel</button>
                <button type="submit">Change password</button>
            </div>
        </form>
    </dialog>
@endsection