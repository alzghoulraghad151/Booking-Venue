@extends('layouts.users')

@section('title', $space->name)

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&amp;family=Raleway:wght@400;500;600;700&amp;display=swap">
    <link rel="stylesheet" href="{{ asset('css/space-details.css') }}">
@endpush

@section('content')
    <div class="space-details-page">
        <div class="space-details">
            <header class="space-details__header">
                <div class="space-details__title-row">
                    <div>
                        <p class="space-details__eyebrow">{{ $venue->name }}</p>
                        <h1>{{ $space->name }}</h1>
                        @if (filled($venue->address))
                            <p>{{ $venue->address }}</p>
                        @endif
                        @if ($reviews->isNotEmpty())
                            <a href="#reviews-heading">{{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }}</a>
                        @endif
                    </div>
                    <a class="space-details__book-link" href="#space-booking-form">Book Now</a>
                </div>
            </header>

            <section class="space-gallery" aria-label="Space photos">
                @forelse ($images as $image)
                    <div class="space-gallery__photo">
                        <img src="{{ $image->image_url }}"
                             alt="{{ $space->name }} — photo {{ $loop->iteration }}"
                             @unless ($loop->first) loading="lazy" @endunless>
                    </div>
                @empty
                    <p class="space-gallery__empty">No photos are available for this space.</p>
                @endforelse
            </section>

            <div class="space-details__sections">
                <section class="space-overview" aria-labelledby="overview-heading">
                    <h2 id="overview-heading">Overview</h2>
                    <p>{{ $space->description ?: 'No description available.' }}</p>

                    <dl class="space-overview__facts">
                        <div>
                            <dt>Capacity</dt>
                            <dd>{{ $space->capacity }} guests</dd>
                        </div>
                        @if ($space->hourly_rate !== null)
                            <div>
                                <dt>Hourly rate</dt>
                                <dd>{{ number_format((float) $space->hourly_rate, 2) }} JOD</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <form id="space-booking-form" class="space-booking-options"
                      method="GET"
                      action="{{ route('bookings.create', ['space' => $space]) }}">
                    <h2 class="space-booking-options__heading">Configure Your Booking</h2>
                    @if (is_string(request()->query('guest_count')))
                        

                        @foreach (['date', 'start_time', 'end_time'] as $field)
                            @if (is_string(request()->query($field)))
                                <input type="hidden" name="{{ $field }}" value="{{ request()->query($field) }}">
                            @endif
                        @endforeach
                    @endif

                    @error('guest_count')
                        <p role="alert">{{ $message }}</p>
                    @enderror
                    <section aria-labelledby="event-type-heading">
                        <h2 id="event-type-heading">Event Type</h2>

                        <label for="event_type_id">Choose an event type (required)</label>
                        <select id="event_type_id" name="event_type_id" required>
                            <option value="">Select an event type</option>

                            @foreach ($eventTypes as $eventType)
                                <option value="{{ $eventType->id }}"
                                        @selected((string) old('event_type_id') === (string) $eventType->id)>
                                    {{ $eventType->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('event_type_id')
                            <p role="alert">{{ $message }}</p>
                        @enderror
                    </section>

                    <section aria-labelledby="layouts-heading">
                        <h2 id="layouts-heading">Space Layouts</h2>

                        @if ($layouts->isEmpty())
                            <p>No layouts are available for this space.</p>
                        @else
                            <p data-layout-status role="status">Choose an event type to see matching layouts.</p>
                            <fieldset class="layout-options" data-layout-options hidden>
                                <legend>Choose one layout (required)</legend>

                                <div class="layout-options__grid">
                                    @foreach ($layouts as $layout)
                                        <label class="layout-option"
                                               data-layout-event-types="{{ implode(' ', $layout->eventTypes->modelKeys()) }}"
                                               hidden>
                                            <input type="radio"
                                                   name="space_layout_id"
                                                   value="{{ $layout->id }}"
                                                   disabled
                                                   @checked((string) old('space_layout_id') === (string) $layout->id)
                                                   required>

                                            <span class="layout-option__card">
                                                <strong>{{ $layout->name }}</strong>
                                                <span>{{ $layout->capacity }} guests</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        @error('space_layout_id')
                            <p role="alert">{{ $message }}</p>
                        @enderror
                    </section>

                    <section aria-labelledby="packages-heading">
                        <h2 id="packages-heading">Related Packages</h2>

                        <p data-package-status role="status">
                            Choose an event type to see matching packages.
                        </p>

                        @if ($packages->isNotEmpty())
                            <fieldset class="layout-options">
                                <legend>Choose a package (optional)</legend>

                                <label class="no-package-option">
                                    <input type="radio"
                                           name="package_choice"
                                           value=""
                                           @checked(! $packages->contains('id', old('package_id')))>
                                    Continue without a package
                                </label>

                                <div class="package-options__grid">
                                    @foreach ($packages as $package)
                                        @include('public.spaces.package-card', ['package' => $package])
                                    @endforeach
                                </div>
                                <nav class="package-pagination" aria-label="Package pages" data-package-pagination hidden></nav>
                            </fieldset>
                        @endif

                        @error('package_id')
                            <p role="alert">{{ $message }}</p>
                        @enderror
                    </section>

                    <button class="space-booking-options__submit" type="submit" form="space-booking-form" disabled>
                        Continue to booking
                    </button>
                </form>

                <section class="space-overview" aria-labelledby="reviews-heading">
                    <h2 id="reviews-heading">Reviews</h2>

                    @forelse ($reviews->take(5) as $review)
                        <article>
                            <div class="space-review__byline">
                                <strong>{{ $review->booking?->customer?->name ?? 'Guest' }}</strong>
                                <span class="space-review__stars" role="img" aria-label="{{ $review->rating }} out of 5 stars">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span aria-hidden="true" @class(['space-review__star--filled' => $star <= $review->rating])>★</span>
                                    @endfor
                                </span>
                            </div>

                            @if (filled($review->comment))
                                <p>{{ $review->comment }}</p>
                            @endif
                        </article>
                    @empty
                        <p>No reviews yet for this space.</p>
                    @endforelse

                    @if ($reviews->count() > 5)
                        <button type="button" class="space-details__more" data-reviews-open>
                            View all {{ $reviews->count() }} reviews
                        </button>

                        <dialog class="space-reviews-dialog" aria-labelledby="all-reviews-heading">
                            <header>
                                <h2 id="all-reviews-heading">All reviews</h2>
                                <form method="dialog">
                                    <button type="submit" class="space-details__more" autofocus>Close</button>
                                </form>
                            </header>
                            @foreach ($reviews as $review)
                                <article>
                                    <div class="space-review__byline">
                                        <strong>{{ $review->booking?->customer?->name ?? 'Guest' }}</strong>
                                        <span class="space-review__stars" role="img" aria-label="{{ $review->rating }} out of 5 stars">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <span aria-hidden="true" @class(['space-review__star--filled' => $star <= $review->rating])>★</span>
                                            @endfor
                                        </span>
                                    </div>
                                    @if (filled($review->comment))
                                        <p>{{ $review->comment }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </dialog>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/space-booking-options.js') }}" defer></script>
@endpush
