@extends('layouts.admin')

@section('title', 'My profile')
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record dar-admin-profile">
        <header class="dar-admin-profile-heading">
            <p>Administrator account</p>
            <h1>My profile</h1>
        </header>

        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif

        <form class="admin-edit-form" action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="dar-profile-field">
                <label for="profile-name">Full name</label>
                <input id="profile-name" name="name" autocomplete="name" value="{{ old('name', $admin->name) }}"
                       required maxlength="255" @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                @error('name')
                    <p id="profile-name-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="dar-profile-field">
                <label for="profile-email">Email address</label>
                <input id="profile-email" name="email" type="email" autocomplete="email" value="{{ old('email', $admin->email) }}"
                       required maxlength="255" @error('email') aria-invalid="true" aria-describedby="profile-email-error" @enderror>
                @error('email')
                    <p id="profile-email-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="dar-profile-field">
                <label for="profile-phone">Phone number <small>(optional)</small></label>
                <input id="profile-phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone', $admin->phone) }}"
                       maxlength="20" @error('phone') aria-invalid="true" aria-describedby="profile-phone-error" @enderror>
                @error('phone')
                    <p id="profile-phone-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <x-admin.edit-actions :back-url="route('admin.dashboard')" back-label="Cancel" save-label="Save changes" />
        </form>
    </div>
@endsection
