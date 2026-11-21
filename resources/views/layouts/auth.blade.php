@extends('layouts.users')

@section('navbar', '')
@section('footer', '')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&amp;family=Raleway:wght@400;500;600;700&amp;display=swap">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
    <div class="auth-page">
        <div class="auth-visual">
            <a class="auth-brand" href="{{ route(Route::has('home') ? 'home' : 'spaces.search') }}">Convene</a>
            <p>Discover and reserve curated spaces for your events.</p>
        </div>
        <div class="auth-card">
            <a class="auth-brand auth-brand--mobile" href="{{ route(Route::has('home') ? 'home' : 'spaces.search') }}">Convene</a>
            @yield('auth-form')
        </div>
    </div>
@endsection
