@extends('layouts.owner')

@section('title', 'My Profile')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-venues.css') }}">
@endpush

@section('content')
<div class="venue-page">
    <h1>My Profile</h1>

    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif

    <form class="venue-card venue-form" method="POST" action="{{ route('owner.profile.update') }}">
        @csrf
        @method('PATCH')

        <div>
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" required>
            @error('name')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
            @error('email')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="10" placeholder="079XXXXXXX" required>
            @error('phone')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="current_password">Current password <small>(required if changing email)</small></label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password">
            @error('current_password')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <p>
           
            &middot; Member since: <strong>{{ $user->created_at?->format('M Y') }}</strong>
        </p>

        <button class="venue-button--primary" type="submit">Save Changes</button>
    </form>
</div>
@endsection