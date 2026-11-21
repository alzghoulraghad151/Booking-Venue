@extends('layouts.users')

@section('title', "Find your space")

@push('styles')
    <meta name="description" content="Find a venue for your next gathering. Explore event spaces by address and guest count with Convene.">
    <link rel="stylesheet" href="{{ asset('css/convene-listing-cards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-landing.css') }}">
@endpush

@section('content')
    <div class="convene-landing">
<div>
        <section class="landing-hero" aria-labelledby="landing-title">
            <div class="landing-hero__content">
                <p class="landing-eyebrow">A space for every gathering</p>
                <h1 id="landing-title">Find the right space for your next event</h1>
                <p class="landing-hero__intro">From meaningful dinners to memorable celebrations, discover a venue that brings people together.</p>
                <a class="landing-button landing-button--warm" href="#explore-locations">Explore spaces <span aria-hidden="true">→</span></a>
            </div>
        </section>

        <form class="landing-search" action="{{ route('spaces.search') }}" method="GET" aria-label="Search event spaces">
            <div class="landing-search__field">
                <label for="landing-address">Location</label>
                <input id="landing-address" name="address" type="text" list="landing-locations" placeholder="Where are you planning?" autocomplete="street-address" required>
                <datalist id="landing-locations">
                    @foreach ($locations as $location)
                        <option value="{{ $location['address'] }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="landing-search__field landing-search__field--guests">
                <label for="landing-capacity">Guests</label>
                <input id="landing-capacity" name="capacity" type="number" min="1" placeholder="Number of guests">
            </div>
            <button class="landing-button landing-button--navy" type="submit">Search spaces <span aria-hidden="true">→</span></button>
        </form>

        <section class="landing-section landing-cities" id="explore-locations" aria-labelledby="locations-title">
            <div class="landing-section__heading">
                <p class="landing-eyebrow landing-eyebrow--sage">Start with a place</p>
                <h2 id="locations-title">Explore spaces by location</h2>
                <p>Choose a venue location to find a space close to where your people are.</p>
            </div>

            <div class="landing-city-grid">
                @forelse ($locations as $location)
                    <a class="landing-city-card" href="{{ route('spaces.search', ['address' => $location['address']]) }}">
                        <img class="landing-card-image" src="{{ $location['image_url'] }}" alt="" loading="lazy">
                        <span class="landing-city-card__content">
                            <span class="landing-city-card__name">{{ $location['address'] }}</span>
                            @if ($location['description'])
                                <span class="landing-city-card__description">{{ \Illuminate\Support\Str::limit($location['description'], 90) }}</span>
                            @endif
                            <span class="landing-city-card__count">{{ $location['venue_count'] }} {{ \Illuminate\Support\Str::plural('venue', $location['venue_count']) }}</span>
                        </span>
                    </a>
                @empty
                    <p>No locations have approved venues yet.</p>
                @endforelse
            </div>
        </section>

        <section class="landing-section landing-venues" aria-labelledby="venues-title">
            <div class="landing-section__heading">
                <p class="landing-eyebrow landing-eyebrow--sage">Places to gather</p>
                <h2 id="venues-title">Venues to explore</h2>
                <p>Browse venues currently available on Convene.</p>
            </div>

            <div class="listing-card-grid landing-venue-grid">
                @forelse ($featuredVenues as $venue)
                    <a class="listing-card" href="{{ route('spaces.search', ['venue_id' => $venue->id]) }}" style="background-image: url(&quot;{{ $venue->images->first()?->image_url }}&quot;)">
                        <div class="listing-card__body">
                            <h3 class="listing-card__title">{{ $venue->name }}</h3>
                            <p class="listing-card__location">{{ $venue->address }}</p>
                            @if ($venue->description)
                                <p class="listing-card__description">{{ \Illuminate\Support\Str::limit($venue->description, 110) }}</p>
                            @endif
                            <div class="listing-card__meta">
                                <span>{{ $venue->active_spaces_count }} {{ \Illuminate\Support\Str::plural('space', $venue->active_spaces_count) }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <p>No approved venues are available yet.</p>
                @endforelse
            </div>
        </section>

        <section class="landing-section landing-venues" aria-labelledby="latest-places-title">
            <div class="landing-section__heading">
                <p class="landing-eyebrow landing-eyebrow--sage">Recently added</p>
                <h2 id="latest-places-title">Latest added places</h2>
                <p>Explore the newest spaces available for your event.</p>
            </div>

            <div class="listing-card-grid">
                @forelse ($latestPlaces as $place)
                    <a class="listing-card" href="{{ route('spaces.show', $place) }}" style="background-image: url(&quot;{{ $place->images->first()?->image_url }}&quot;)">
                        <div class="listing-card__body">
                            <h3 class="listing-card__title">{{ $place->name }}</h3>
                            <p class="listing-card__location">{{ $place->venue->name }} · {{ $place->venue->address }}</p>
                            @if ($place->description)
                                <p class="listing-card__description">{{ \Illuminate\Support\Str::limit($place->description, 100) }}</p>
                            @endif
                            <div class="listing-card__meta">
                                <span>{{ $place->capacity }} {{ \Illuminate\Support\Str::plural('guest', $place->capacity) }}</span>
                                @if ($place->hourly_rate !== null)
                                    <span>{{ number_format((float) $place->hourly_rate, 2) }} JOD / hour</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <p>No active places have been added yet.</p>
                @endforelse
            </div>
        </section>

        <section class="landing-occasion" aria-labelledby="occasion-title">
            <div class="landing-occasion__intro">
                <p class="landing-eyebrow landing-eyebrow--warm">Make room for a good moment</p>
                <h2 id="occasion-title">Spaces for every kind of gathering</h2>
                <p>Find a setting that feels right for the people, plans, and moments that matter.</p>
            </div>
            <div class="landing-occasion__cards">
                @forelse ($occasionCards as $card)
                    <a class="landing-occasion-card" href="{{ route('spaces.search', ['event_type_id' => $card['id']]) }}">
                        <img class="landing-card-image" src="{{ $card['image_url'] }}" alt="" loading="lazy">
                        <h3>{{ $card['name'] }}</h3>
                        @if ($card['description'])
                            <p>{{ $card['description'] }}</p>
                        @endif
                    </a>
                @empty
                    <p>No event types have available spaces yet.</p>
                @endforelse
            </div>
        </section>
    </div>
    </div>
@endsection
