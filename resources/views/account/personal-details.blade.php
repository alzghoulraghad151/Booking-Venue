@extends('layouts.account')

@section('title', 'Personal Details')

@section('content')
    <h1>Personal Details</h1>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <section class="account-photo">
        <h2>Profile Photo</h2>

        @if ($user->profile_photo_url)
            <img src="{{ $user->profile_photo_url }}" alt="Profile photo" width="112">
        @endif

        <form method="POST" action="{{ route('account.profile-photo.update') }}"
              enctype="multipart/form-data">
            @csrf

            <input id="photo" name="photo" type="file"
                   accept="image/jpeg,image/png,image/webp" hidden>

            <button type="button" id="choose-profile-photo">
                {{ $user->profile_photo_url ? 'Change photo' : 'Upload photo' }}
            </button>

            @error('photo')
                <p role="alert">{{ $message }}</p>
            @enderror
        </form>

        @if ($user->profile_photo_url)
            <form method="POST" action="{{ route('account.profile-photo.destroy') }}">
                @csrf
                @method('DELETE')

                <button type="submit">Delete photo</button>
            </form>
        @endif
    </section>

    <form method="POST" action="{{ route('account.personal-details.update') }}">
        @csrf
        @method('PATCH')

        <div>
            <label for="name">Full name</label>
            <input id="name" name="name" type="text"
                   value="{{ old('name', $user->name) }}"
                   autocomplete="name" maxlength="255" required>

            @error('name')
                <p role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="display-email">Email address</label>

            <div class="account-email-row">
                <input id="display-email" type="email"
                       value="{{ $user->email }}" readonly>

                <button type="button" id="edit-email" aria-label="Edit email address">
                    Edit
                </button>
            </div>
        </div>

        <div>
            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="tel"
                   value="{{ old('phone', $user->phone) }}"
                   autocomplete="tel" maxlength="20">

            @error('phone')
                <p role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Save changes</button>
    </form>

    <dialog id="email-dialog" aria-labelledby="email-dialog-title"
            @if ($errors->has('email') || $errors->has('current_password'))
                data-show-errors
            @endif>
        <h2 id="email-dialog-title">Change email address</h2>

        <form method="POST" action="{{ route('account.email.update') }}">
            @csrf
            @method('PATCH')

            <div>
                <label for="email">New email address</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" autocomplete="email"
                       maxlength="255" required autofocus>

                @error('email')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password"
                       autocomplete="current-password" required>

                @error('current_password')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="button" id="cancel-email">Cancel</button>
            <button type="submit">Save email</button>
        </form>
    </dialog>

    <script>
        const photoInput = document.getElementById('photo');

        document.getElementById('choose-profile-photo').addEventListener('click', () => {
            photoInput.click();
        });

        photoInput.addEventListener('change', () => {
            if (photoInput.files.length > 0) {
                photoInput.form.requestSubmit();
            }
        });

        const emailDialog = document.getElementById('email-dialog');
        const passwordInput = document.getElementById('current_password');

        document.getElementById('edit-email').addEventListener('click', () => {
            emailDialog.showModal();
        });

        document.getElementById('cancel-email').addEventListener('click', () => {
            emailDialog.close();
        });

        emailDialog.addEventListener('close', () => {
            passwordInput.value = '';
        });

        if (emailDialog.hasAttribute('data-show-errors')) {
            emailDialog.showModal();
        }
    </script>
@endsection