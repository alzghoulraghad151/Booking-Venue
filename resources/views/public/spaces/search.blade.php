@extends('layouts.users')

@section('title', "Find places")

@php
    $cl = fn (?string $url, int $w) => $url && str_contains($url, 'res.cloudinary.com/') && ! preg_match('#/upload/[a-z]_#', $url)
        ? preg_replace('#/upload/#', '/upload/w_'.$w.',c_limit,q_auto,f_auto/', $url, 1)
        : $url;
    $heart = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-pagination.css') }}">
@endpush

@section('content')
<div class="nvp nvp-search">
    {{-- Navy band with the search bar and filters --}}
    <section class="nvp-band" aria-labelledby="places-title">
        <div class="nvp-wrap">
            <p class="nvp-eyebrow">Explore spaces</p>
            <h1 id="places-title">Find the right space for your event</h1>
            <p class="nvp-lead">Bookable rooms across our partner venues in Jordan. Filter by city, event type, guests and hourly rate.</p>

            <form class="nvp-filters" action="{{ route('spaces.search') }}" method="GET">
                <div class="nvp-filters__main">
                    <label class="nvp-field nvp-field--wide">
                        <span>Search</span>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Name, venue, event type or details" autocomplete="off">
                    </label>
                    <label class="nvp-field">
                        <span>City</span>
                        <select id="address" name="address">
                            <option value="">All of Jordan</option>
                            @foreach ($venues->pluck('address')->filter()->unique()->sort() as $venueAddress)
                                <option value="{{ $venueAddress }}" @selected($address === $venueAddress)>{{ $venueAddress }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="nvp-field">
                        <span>Event type</span>
                        <select id="filter-event-type" name="event_type_id">
                            <option value="">Any type</option>
                            @foreach ($eventTypes as $eventType)
                                <option value="{{ $eventType->id }}" @selected((string) $eventTypeId === (string) $eventType->id)>{{ $eventType->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="nvp-field nvp-field--narrow">
                        <span>Guests</span>
                        <input id="capacity" name="capacity" type="number" min="1" value="{{ $capacity }}" placeholder="Any size">
                    </label>
                    <button class="nvp-go" type="submit" aria-label="Apply filters">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <span>Search</span>
                    </button>
                </div>

                <div class="nvp-filters__more">
                    <label class="nvp-chipfield">
                        <span>Venue</span>
                        <select id="filter-venue" name="venue_id">
                            <option value="">All venues</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" @selected((string) $venueId === (string) $venue->id)>{{ $venue->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="nvp-chipfield">
                        <span>From (JOD/hr)</span>
                        <input id="filter-min-rate" name="min_rate" type="number" min="0" step="0.01" value="{{ $minRate }}" placeholder="Min">
                    </label>
                    <label class="nvp-chipfield">
                        <span>To (JOD/hr)</span>
                        <input name="max_rate" type="number" min="0" step="0.01" value="{{ $maxRate }}" placeholder="Max" aria-label="Maximum hourly rate">
                    </label>
                    <label class="nvp-chipfield">
                        <span>Sort</span>
                        <select id="filter-sort" name="sort">
                            <option value="newest" @selected($sort === 'newest')>Recently added</option>
                            <option value="price_low" @selected($sort === 'price_low')>Price: low to high</option>
                            <option value="price_high" @selected($sort === 'price_high')>Price: high to low</option>
                            <option value="capacity" @selected($sort === 'capacity')>Guest capacity</option>
                            <option value="rating" @selected($sort === 'rating')>Top rated</option>
                            <option value="name" @selected($sort === 'name')>Name</option>
                        </select>
                    </label>
                    @if ($searched)
                        <a class="nvp-reset" href="{{ route('spaces.search') }}">Clear filters</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <div class="nvp-wrap nvp-body">
        @if (session('favourite_status'))
            <p class="nvp-note" role="status">{{ session('favourite_status') }}</p>
        @endif

        @if ($voiceSummary)
            <section class="nvp-voice" aria-live="polite">
                <span class="nvp-voice__mark" aria-hidden="true"></span>
                <div>
                    <p>{{ $voiceSummary }}</p>
                    @if ($voiceSummaryAudioUrl)
                        <audio id="voice-summary-audio" src="{{ $voiceSummaryAudioUrl }}" preload="none"></audio>
                        <button type="button" class="nvp-pill" id="voice-summary-play">Play summary</button>
                        <p id="voice-summary-status" role="status"></p>
                    @endif
                </div>
            </section>
        @endif

        <div class="nvp-results-head">
            <h2>{{ number_format($spaces->total()) }} {{ \Illuminate\Support\Str::plural('space', $spaces->total()) }} <em>available</em></h2>
            <p>Showing {{ number_format($spaces->count()) }} on this page</p>
        </div>

        <section class="nvp-grid" aria-label="Available places">
            @forelse ($spaces as $space)
                @php($image = $space->images->first())
                @php($imageUrls = $space->images->pluck('image_url')->map(fn ($u) => $cl($u, 900))->values())
                @php($isFavorite = in_array($space->id, $favoriteSpaceIds))
                <article class="nvp-card place-card">
                    <div class="nvp-card__media place-card__media" data-card-images='@json($imageUrls)'>
                        <a class="nvp-card__photo" href="{{ route('spaces.show', $space) }}" aria-label="View {{ $space->name }} at {{ $space->venue->name }}">
                            @if ($image)
                                <img class="place-card__image" src="{{ $cl($image->image_url, 900) }}" alt="{{ $space->name }}" loading="lazy">
                            @else
                                <span class="place-card__image place-card__image--empty" aria-hidden="true"></span>
                            @endif
                        </a>

                        @if ($space->hourly_rate !== null)
                            <span class="nvp-chip">From {{ rtrim(rtrim(number_format((float) $space->hourly_rate, 2), '0'), '.') }} JOD<small> / hr</small></span>
                        @endif

                        @if ($space->images->count() > 1)
                            <button class="nvp-arrow place-card__image-arrow place-card__image-arrow--previous" type="button" aria-label="Previous image"><span aria-hidden="true">‹</span></button>
                            <button class="nvp-arrow place-card__image-arrow place-card__image-arrow--next" type="button" aria-label="Next image"><span aria-hidden="true">›</span></button>
                        @endif

                        @if (auth()->check() && auth()->user()->role === 'organizer')
                            <form class="place-card__favorite-form nvp-heart-form" action="{{ route('spaces.favorite.toggle', $space) }}" method="POST">
                                @csrf
                                <button class="nvp-heart place-card__favorite @if ($isFavorite) is-active @endif" type="submit" aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}">{!! $heart !!}</button>
                            </form>
                        @else
                            <a class="nvp-heart" href="{{ route('login') }}" aria-label="Sign in to add to favorites">{!! $heart !!}</a>
                        @endif

                        <div class="nvp-card__meta">
                            <span class="nvp-card__venue">{{ $space->venue->name }}@if ($space->venue->address) · {{ $space->venue->address }}@endif</span>
                            <h3><a href="{{ route('spaces.show', $space) }}">{{ $space->name }}</a></h3>
                        </div>
                    </div>

                    <div class="nvp-card__body">
                        @if ($space->description)
                            <p class="nvp-card__desc">{{ \Illuminate\Support\Str::limit($space->description, 110) }}</p>
                        @endif
                        <div class="nvp-card__facts">
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.4-5.5 6.5-5.5s5.7 1.9 6.5 5.5"/></svg> Up to {{ $space->capacity }} guests</span>
                            @if ((int) $space->venue_review_count > 0)
                                <span aria-label="Rated {{ number_format((float) $space->venue_average_rating, 1) }} out of 5 from {{ $space->venue_review_count }} reviews"><b aria-hidden="true">★</b> {{ number_format((float) $space->venue_average_rating, 1) }} <small>({{ $space->venue_review_count }})</small></span>
                            @endif
                        </div>
                        <a class="nvp-card__cta" href="{{ route('spaces.show', $space) }}">View details <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @empty
                <div class="nvp-empty">
                    <h3>No places match these filters</h3>
                    <p>Try another city, number of guests, event type or price range.</p>
                    <a class="nvp-pill nvp-pill--dark" href="{{ route('spaces.search') }}">See all places</a>
                </div>
            @endforelse
        </section>

        @include('partials.site.pagination', ['paginator' => $spaces, 'label' => 'Places pages'])
    </div>
</div>
@endsection

@push('scripts')
    @if ($voiceSummary && $voiceSummaryAudioUrl)
        <script type="module" src="{{ asset('js/space-search-summary-audio.js') }}"></script>
    @endif
    <script src="{{ asset('js/space-card-gallery.js') }}" defer></script>
    <script src="{{ asset('js/space-favorites.js') }}" defer></script>
@endpush
