@extends('layouts.users')

@section('title', 'Booking details')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/convene-booking-confirm.css') }}">
@endpush

@php($backUrl = Route::has('spaces.show') ? route('spaces.show', $space) : url()->previous())
@php($homeUrl = Route::has('home') ? route('home') : route('spaces.search'))

{{-- Header and footer come from the shared layout (layouts.users -> partials/site). --}}
@section('content')
<div class="bc-wrap">
<div class="bc-page">
    <header class="bc-header">
        <nav class="bc-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ $homeUrl }}">Home</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('spaces.search') }}">Explore Spaces</a>
            <span aria-hidden="true">/</span>
            <a href="{{ $backUrl }}">{{ $space->name }}</a>
            <span aria-hidden="true">/</span>
            <span class="bc-breadcrumb__current">Booking details</span>
        </nav>
        <span class="bc-eyebrow">Confirm reservation request</span>
        <h1 class="bc-title">Booking details</h1>
        <p class="bc-subtitle">Check your selection, choose the date and time, then confirm your request.</p>
    </header>

    @if (session('success'))
        <div class="bc-alert bc-alert--success" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="bc-alert bc-alert--error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="bc-form"
          class="bc-layout"
          action="{{ route('bookings.store', $space) }}"
          method="POST"
          data-hourly-rate="{{ $space->hourly_rate }}"
          data-package-price="{{ $package?->price ?? 0 }}"
          data-has-package="{{ $package ? 1 : 0 }}"
          data-included-hours="{{ $package ? \App\Services\BookingPriceCalculator::PACKAGE_INCLUDED_HOURS : 0 }}"
          novalidate>
        @csrf

        {{-- Read-only selection coming from the space details page. --}}
        <input type="hidden" name="event_type_id" value="{{ $eventType->id }}">
        <input type="hidden" name="space_layout_id" value="{{ $layout->id }}">
        @if ($package)
            <input type="hidden" name="package_id" value="{{ $package->id }}">
        @endif

        {{-- Main column: date and time + actions --}}
        <div class="bc-col bc-col--main">
            <section class="bc-panel bc-when" aria-labelledby="bc-when-heading">
                <div class="bc-panel-head">
                    <h2 id="bc-when-heading">Date and time</h2>
                    <p class="bc-muted">Times are in Amman time (GMT+3). The event must start and end on the same day.</p>
                </div>

                <div class="bc-fields">
                    <div class="bc-field bc-field--date">
                        <label for="date">Date</label>
                        <input id="date" name="date" type="date" min="{{ $minDate }}" value="{{ old('date', $bookingPrefill['date'] ?? '') }}" required>
                        @error('date') <p class="bc-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="bc-field">
                        <label for="start_time">Start time</label>
                        <input id="start_time" name="start_time" type="time" step="60" value="{{ old('start_time', $bookingPrefill['start_time'] ?? '') }}" required>
                        @error('start_time') <p class="bc-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="bc-field">
                        <label for="end_time">End time</label>
                        <input id="end_time" name="end_time" type="time" step="60" value="{{ old('end_time', $bookingPrefill['end_time'] ?? '') }}" required>
                        @error('end_time') <p class="bc-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="bc-actions">
                <button class="bc-button bc-button--primary" type="submit">Confirm booking</button>
                <a class="bc-button bc-button--quiet" href="{{ $backUrl }}">Back to space</a>
            </div>
        </div>

        {{-- Side column: selected space + price summary --}}
        <div class="bc-col bc-col--side">
            <section class="bc-panel bc-selection" aria-labelledby="bc-selection-heading">
                @if ($imageUrl)
                    <img class="bc-venue__image" src="{{ $imageUrl }}" alt="{{ $space->name }}" referrerpolicy="no-referrer"
                         onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                @endif
                <div class="bc-venue__image bc-venue__image--empty" @if ($imageUrl) hidden @endif>No photo available</div>

                <div class="bc-selection__body">
                    <div class="bc-venue">
                        <span class="bc-eyebrow">Selected space</span>
                        <h2 id="bc-selection-heading">{{ $space->name }}</h2>
                        <p class="bc-muted">{{ $venue->name }} &middot; {{ $venue->address }}</p>
                    </div>

                    <dl class="bc-info-list">
                        <div class="bc-info-item">
                            <dt>Event type</dt>
                            <dd>{{ $eventType->name }}</dd>
                        </div>
                        <div class="bc-info-item">
                            <dt>Space layout</dt>
                            <dd>{{ $layout->name }} <span class="bc-muted">(up to {{ number_format($layout->capacity) }} guests)</span></dd>
                        </div>
                        <div class="bc-info-item">
                            <dt>Package</dt>
                            <dd>
                                @if ($package)
                                    {{ $package->name }} <span class="bc-muted">({{ number_format((float) $package->price, 2) }} JOD)</span>
                                @else
                                    No package
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="bc-panel bc-pricing" aria-labelledby="bc-price-heading">
                <div class="bc-panel-row">
                    <h2 id="bc-price-heading">Price summary</h2>
                </div>

                <dl class="bc-price">
                    <div class="bc-price__row">
                        <dt>Duration</dt>
                        <dd><span id="bc-hours">&mdash;</span> h</dd>
                    </div>
                    @if ($package)
                        <div class="bc-price__row">
                            <dt>Package: {{ $package->name }} <span class="bc-muted">(includes 1 hour of the space)</span></dt>
                            <dd>{{ number_format((float) $package->price, 2) }} JOD</dd>
                        </div>
                        <div class="bc-price__row">
                            <dt>Extra hours ({{ number_format((float) $space->hourly_rate, 2) }} JOD / h) &times; <span id="bc-extra-hours">&mdash;</span></dt>
                            <dd><span id="bc-space-price">&mdash;</span> JOD</dd>
                        </div>
                    @else
                        <div class="bc-price__row">
                            <dt>Space ({{ number_format((float) $space->hourly_rate, 2) }} JOD / h)</dt>
                            <dd><span id="bc-space-price">&mdash;</span> JOD</dd>
                        </div>
                    @endif
                    <div class="bc-price__row bc-price__row--total">
                        <dt>Total</dt>
                        <dd><span id="bc-total">&mdash;</span> JOD</dd>
                    </div>
                </dl>

                <p id="bc-price-hint" class="bc-muted" aria-live="polite">Choose a start and end time to see the total.</p>
            </section>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/booking-confirm.js') }}"></script>
@endpush