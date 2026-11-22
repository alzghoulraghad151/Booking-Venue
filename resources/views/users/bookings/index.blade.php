@extends('layouts.users')

@section('title', 'My bookings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-my-bookings-dar.css') }}">
@endpush

@section('content')
    <div class="site-container site-page site-bookings">
        <header class="site-bookings-heading">
            <div>
                <p class="site-bookings-eyebrow">Organizer dashboard</p>
                <h1>My bookings</h1>
                <p>Track the status of your venue bookings.</p>
            </div>
        </header>

        @if (session('success'))
            <p class="site-booking-notice" role="status">{{ session('success') }}</p>
        @endif

        @error('review')
            <p class="site-booking-notice" role="alert">{{ $message }}</p>
        @enderror

        @forelse ($bookings as $booking)
            @if ($loop->first)
                <ul class="site-booking-list" aria-label="Your bookings">
            @endif
            <li class="site-booking-card">
                <div>
                    <p class="site-booking-reference">Booking #{{ $booking->id }}</p>
                    <h2>{{ $booking->venue?->name ?? 'Venue unavailable' }}</h2>
                    @if ($booking->review)
                        <p class="site-booking-reference">Your rating: {{ $booking->review->rating }} / 5</p>
                    @endif
                </div>
                <div class="site-booking-indicators">
                    @if ($booking->has_paid_payment)
                        <span class="site-booking-status site-booking-status--paid">Paid</span>
                    @endif
                    <span @class(['site-booking-status', 'site-booking-status--' . $booking->status])>{{ ucfirst($booking->status) }}</span>
                </div>
                @php($canReview = $booking->status === 'completed' && $booking->has_paid_payment)
                @if ($booking->details->isNotEmpty() || $canReview)
                    @include('users.bookings.actions', ['booking' => $booking, 'page' => $bookings->currentPage()])
                @endif
            </li>
            @if ($loop->last)
                </ul>
            @endif
        @empty
            <section class="site-bookings-empty" aria-labelledby="booking-empty-title">
                <h2 id="booking-empty-title">{{ $bookings->onFirstPage() ? 'No bookings yet' : 'No bookings on this page' }}</h2>
                @if ($bookings->onFirstPage())
                    <p>Browse spaces for your next meeting or company event.</p>
                    <a class="site-button" href="{{ route('spaces.search') }}">Browse spaces</a>
                @endif
            </section>
        @endforelse

        @if ($bookings->hasPages())
            <nav class="site-booking-pagination" aria-label="Booking pages">
                @if ($bookings->previousPageUrl())
                    <a class="site-link site-account-link" href="{{ $bookings->previousPageUrl() }}" rel="prev"><span aria-hidden="true">←</span> Previous</a>
                @endif
                @if ($bookings->nextPageUrl())
                    <a class="site-link site-account-link" href="{{ $bookings->nextPageUrl() }}" rel="next">Next <span aria-hidden="true">→</span></a>
                @endif
            </nav>
        @endif
    </div>
@endsection
