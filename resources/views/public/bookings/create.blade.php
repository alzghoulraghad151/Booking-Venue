@extends('layouts.users')

@section('title', "Booking details")

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-booking-confirm.css') }}">
@endpush

@section('content')
    <div class="bc-standalone">
<div class="bc-page">
    <header>
        <p class="bc-eyebrow">Step 2 of 2</p>
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

    <section class="bc-panel" aria-labelledby="bc-selection-heading">
        <div class="bc-venue">
            @if ($imageUrl)
                <img class="bc-venue__image" src="{{ $imageUrl }}" alt="{{ $space->name }}">
            @endif
            <div>
                <h2 id="bc-selection-heading">{{ $space->name }}</h2>
                <p class="bc-muted">{{ $venue->name }} &middot; {{ $venue->address }}</p>
            </div>
        </div>

        <dl class="bc-info-grid">
            <div class="bc-info-item">
                <dt>Event type</dt>
                <dd>{{ $eventType->name }}</dd>
            </div>
            <div class="bc-info-item">
                <dt>Space layout</dt>
                <dd>{{ $layout->name }} <span class="bc-muted">(up to {{ number_format($layout->capacity) }} guests)</span></dd>
            </div>
            <div class="bc-info-item">
                <dt>Guests</dt>
                <dd>{{ $guestCount === null ? 'Not specified' : number_format($guestCount) }}</dd>
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
    </section>

    <form id="bc-form"
          action="{{ route('bookings.store', $space) }}"
          method="POST"
          data-hourly-rate="{{ $space->hourly_rate }}"
          data-package-price="{{ $package?->price ?? 0 }}"
          novalidate>
        @csrf

        {{-- Read-only selection coming from the space details page. --}}
        <input type="hidden" name="event_type_id" value="{{ $eventType->id }}">
        <input type="hidden" name="space_layout_id" value="{{ $layout->id }}">
        @if ($package)
            <input type="hidden" name="package_id" value="{{ $package->id }}">
        @endif
        @if ($guestCount !== null)
            <input type="hidden" name="guest_count" value="{{ $guestCount }}">
        @endif

        <section class="bc-panel" aria-labelledby="bc-when-heading">
            <h2 id="bc-when-heading">Date and time</h2>
            <p class="bc-muted">Times are in Amman time. The event must start and end on the same day.</p>

            <div class="bc-fields">
                <div class="bc-field">
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" min="{{ $minDate }}" value="{{ old('date', request()->query('date')) }}" required>
                    @error('date') <p class="bc-field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="bc-field">
                    <label for="start_time">Start time</label>
                    <input id="start_time" name="start_time" type="time" step="60" value="{{ old('start_time', request()->query('start_time')) }}" required>
                    @error('start_time') <p class="bc-field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="bc-field">
                    <label for="end_time">End time</label>
                    <input id="end_time" name="end_time" type="time" step="60" value="{{ old('end_time', request()->query('end_time')) }}" required>
                    @error('end_time') <p class="bc-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="bc-panel" aria-labelledby="bc-price-heading">
            <div class="bc-panel-row">
                <h2 id="bc-price-heading">Price summary</h2>
           
            </div>

            <dl class="bc-price">
                <div class="bc-price__row">
                    <dt>Duration</dt>
                    <dd><span id="bc-hours">&mdash;</span> h</dd>
                </div>
                <div class="bc-price__row">
                    <dt>Space </dt>
                    <dd><span id="bc-space-price">&mdash;</span> JOD</dd>
                </div>
                @if ($package)
                    <div class="bc-price__row">
                        <dt>Package: {{ $package->name }}</dt>
                        <dd>{{ number_format((float) $package->price, 2) }} JOD</dd>
                    </div>
                @endif
                <div class="bc-price__row bc-price__row--total">
                    <dt>Total</dt>
                    <dd><span id="bc-total">&mdash;</span> JOD</dd>
                </div>
            </dl>

            <p id="bc-price-hint" class="bc-muted" aria-live="polite">Choose a start and end time to see the total.</p>
      
        </section>

        <div class="bc-actions">
            <button class="bc-button bc-button--primary" type="submit">Confirm booking</button>
            @php
                $returnPrefill = array_filter([
                    'date' => old('date', request()->query('date')),
                    'start_time' => old('start_time', request()->query('start_time')),
                    'end_time' => old('end_time', request()->query('end_time')),
                    'guest_count' => $guestCount,
                ], static fn ($value) => (is_string($value) && $value !== '') || is_int($value));
            @endphp
            <a class="bc-button bc-button--quiet"
               href="{{ Route::has('spaces.show') ? route('spaces.show', ['space' => $space] + $returnPrefill) : url()->previous() }}">
                Back to space
            </a>
        </div>
    </form>
</div>


    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/booking-confirm.js') }}"></script>
@endpush
