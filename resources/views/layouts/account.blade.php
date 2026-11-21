@extends('layouts.users')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush

@section('content')
    <div class="account-page">
        <div class="account-layout">
            <nav class="account-nav" aria-label="Account">
                <a href="{{ route('account.personal-details.edit') }}"
                   @if (request()->routeIs('account.personal-details.edit')) aria-current="page" @endif>
                    Personal Details
                </a>

                <a href="{{ route('account.settings') }}"
                   @if (request()->routeIs('account.settings')) aria-current="page" @endif>
                    Account Settings
                </a>
            </nav>

            <div class="account-content @yield('content-class')">
                @yield('account-content')
            </div>
        </div>
    </div>
@endsection