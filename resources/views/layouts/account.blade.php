@extends('layouts.users')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush

@section('content')
    <div class="account-page">
        <div class="account-layout">
            <nav class="account-nav" aria-label="Account">
                <a href="{{ route('account.personal-details.edit') }}"
                   @if (request()->routeIs('account.personal-details.edit'))
                       aria-current="page"
                   @endif>
                    <svg class="account-nav__icon" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true" focusable="false">
                        <circle cx="12" cy="8" r="3"></circle>
                        <path d="M5 20v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2z"></path>
                    </svg>
                    <span>Personal Details</span>
                </a>

                <a href="{{ route('account.settings') }}"
                   @if (request()->routeIs('account.settings'))
                       aria-current="page"
                   @endif>
                    <svg class="account-nav__icon" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true" focusable="false">
                        <path d="M10 3h4l.5 3 2 .9 2.7-1.2 2 3.5-2.2 2v2l2.2 2-2 3.5-2.7-1.2-2 .9-.5 3h-4l-.5-3-2-.9-2.7 1.2-2-3.5 2.2-2v-2l-2.2-2 2-3.5 2.7 1.2 2-.9z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Account Settings</span>
                </a>

                @if (\Illuminate\Support\Facades\Route::has('account.favourites.index'))
                    <a href="{{ route('account.favourites.index') }}"
                       @if (request()->routeIs('account.favourites.index'))
                           aria-current="page"
                       @endif>
                        <svg class="account-nav__icon" width="18" height="18"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true" focusable="false">
                            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8l8.8 8.8 8.8-8.8a5.5 5.5 0 0 0 0-7.8z"></path>
                        </svg>
                        <span>Favourites</span>
                    </a>
                @endif
            </nav>

            <div class="account-content @yield('content-class')">
                @yield('account-content')
            </div>
        </div>
    </div>
@endsection