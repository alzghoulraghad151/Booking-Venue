@extends('layouts.users')

@section('title', "Find places")

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-place-results.css') }}">
@endpush

@section('content')
    <div class="places-page">
<div class="places-shell">
        <header class="places-heading">
            <h1>Explore Spaces</h1>
            <p>Browse bookable spaces across our partner venues, filtered by address, capacity, event type, and hourly rate.</p>
        </header>

        <form class="places-filters" action="{{ route('spaces.search') }}" method="GET">
            <div class="places-filters__grid">
                <div class="places-filter-field places-filter-field--search">
                    <label for="search">Search places</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Name, venue, event type, or details" autocomplete="off">
                </div>

                <div class="places-filter-field places-filter-field--address">
                    <label for="address">Address</label>
                    <select id="address" name="address">
                        <option value="">All addresses</option>
                        @foreach ($venues->pluck('address')->filter()->unique()->sort() as $venueAddress)
                            <option value="{{ $venueAddress }}" @selected($address === $venueAddress)>{{ $venueAddress }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="places-filter-field">
                    <label for="filter-venue">Venue</label>
                    <select id="filter-venue" name="venue_id">
                        <option value="">All venues</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}" @selected((string) $venueId === (string) $venue->id)>{{ $venue->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="places-filter-field">
                    <label for="filter-event-type">Event type</label>
                    <select id="filter-event-type" name="event_type_id">
                        <option value="">All event types</option>
                        @foreach ($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" @selected((string) $eventTypeId === (string) $eventType->id)>{{ $eventType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="places-filter-field">
                    <label for="capacity">Guests</label>
                    <input id="capacity" name="capacity" type="number" min="1" value="{{ $capacity }}" placeholder="Any size">
                </div>

                <div class="places-filter-field places-filter-field--price">
                    <label for="filter-min-rate">Hourly rate (JOD)</label>
                    <div class="places-filter-field__range">
                        <input id="filter-min-rate" name="min_rate" type="number" min="0" step="0.01" value="{{ $minRate }}" placeholder="Min">
                        <span aria-hidden="true">–</span>
                        <input name="max_rate" type="number" min="0" step="0.01" value="{{ $maxRate }}" placeholder="Max" aria-label="Maximum hourly rate">
                    </div>
                </div>

                <div class="places-filter-field">
                    <label for="filter-sort">Sort by</label>
                    <select id="filter-sort" name="sort">
                        <option value="newest" @selected($sort === 'newest')>Recently added</option>
                        <option value="price_low" @selected($sort === 'price_low')>Price: low to high</option>
                        <option value="price_high" @selected($sort === 'price_high')>Price: high to low</option>
                        <option value="capacity" @selected($sort === 'capacity')>Guest capacity</option>
                        <option value="rating" @selected($sort === 'rating')>Top rated</option>
                        <option value="name" @selected($sort === 'name')>Name</option>
                    </select>
                </div>

                <div class="places-filters__actions">
                    <button class="places-button" type="submit">Apply filters</button>
                    @if ($searched)
                        <a class="places-reset" href="{{ route('spaces.search') }}">Clear filters</a>
                    @endif
                </div>
            </div>
        </form>

        @if (session('favourite_status'))
            <p role="status">{{ session('favourite_status') }}</p>
        @endif

        @if ($voiceSummary)
            <section class="places-voice-summary" aria-live="polite">
                <p>{{ $voiceSummary }}</p>
                @if ($voiceSummaryAudioUrl)
                    <audio id="voice-summary-audio" src="{{ $voiceSummaryAudioUrl }}" preload="none"></audio>
                    <button type="button" id="voice-summary-play">Play summary</button>
                    <p id="voice-summary-status" role="status"></p>
                @endif
            </section>
        @endif

        <div class="places-results-heading">
            <h2>Showing {{ number_format($spaces->count()) }} of {{ number_format($spaces->total()) }} {{ \Illuminate\Support\Str::plural('space', $spaces->total()) }}</h2>
        </div>

        <section class="places-results-grid" aria-label="Available places">
            @forelse ($spaces as $space)
                @php($image = $space->images->first())
                @php($imageUrls = $space->images->pluck('image_url')->values())
                @php($isFavorite = in_array($space->id, $favoriteSpaceIds))
                @php($eventLabels = $space->spaceLayouts->flatMap(fn ($layout) => $layout->eventTypes)->unique('id')->take(4))
                <article class="place-card" data-component-id="card-action" data-component-type="card" data-component-variant="action">
                    <div class="place-card__media" data-card-images='@json($imageUrls)'>
                        <a class="place-card__photo-link" href="{{ route('spaces.show', $space) }}" aria-label="View {{ $space->name }} at {{ $space->venue->name }}">
                            @if ($image)
                                <img class="place-card__image" src="{{ $image->image_url }}" alt="{{ $space->name }}" loading="lazy">
                            @else
                                <span class="place-card__image place-card__image--empty" aria-hidden="true"></span>
                            @endif
                        </a>

                        @if ($space->images->count() > 1)
                            <button class="place-card__image-arrow place-card__image-arrow--previous" type="button" aria-label="Previous image"><span aria-hidden="true">‹</span></button>
                            <button class="place-card__image-arrow place-card__image-arrow--next" type="button" aria-label="Next image"><span aria-hidden="true">›</span></button>
                        @endif

                        @auth
                            @if (auth()->user()->role === 'organizer')
                                <form class="place-card__favorite-form" action="{{ route('spaces.favorite.toggle', $space) }}" method="POST">
                                    @csrf
                                    <button class="place-card__favorite @if ($isFavorite) is-active @endif" type="submit" aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
                                    </button>
                                </form>
                            @else
                                <a class="place-card__favorite" href="{{ route('login') }}" aria-label="Sign in to add to favorites"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg></a>
                            @endif
                        @else
                            <a class="place-card__favorite" href="{{ route('login') }}" aria-label="Sign in to add to favorites"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg></a>
                        @endauth
                    </div>

                    <div class="place-card__body">
                        <header class="place-card__content">
                            <span class="place-card__venue">{{ $space->venue->name }}@if ($space->venue->address) · {{ $space->venue->address }}@endif</span>
                            <h3><a href="{{ route('spaces.show', $space) }}">{{ $space->name }}</a></h3>
                            @if ($space->description)
                                <p class="place-card__description">{{ \Illuminate\Support\Str::limit($space->description, 145) }}</p>
                            @endif
                        </header>

                        @if ((int) $space->venue_review_count > 0)
                            <div class="place-card__rating" aria-label="Rated {{ number_format((float) $space->venue_average_rating, 1) }} out of 5 from {{ $space->venue_review_count }} reviews">
                                <span class="place-card__stars" aria-hidden="true">{{ str_repeat('★', (int) round((float) $space->venue_average_rating)) }}{{ str_repeat('☆', 5 - (int) round((float) $space->venue_average_rating)) }}</span>
                                <strong>{{ number_format((float) $space->venue_average_rating, 1) }}</strong>
                                <span>({{ $space->venue_review_count }})</span>
                            </div>
                        @endif

                        <footer class="place-card__footer">
                            <div class="place-card__footer-details">
                                <span class="place-card__capacity"><span aria-hidden="true">♙</span> {{ $space->capacity }} Guests</span>
                                @if ($space->hourly_rate !== null)
                                    <strong>{{ number_format((float) $space->hourly_rate, 2) }} JOD / hr</strong>
                                @endif
                            </div>
                            <a class="place-card__action" href="{{ route('spaces.show', $space) }}">View space details</a>
                        </footer>
                    </div>
                </article>
            @empty
                <div class="places-empty">
                    <h3>No places match these filters</h3>
                    <p>Try another address, capacity, event type, or price range.</p>
                    <a class="places-button" href="{{ route('spaces.search') }}">See all places</a>
                </div>
            @endforelse
        </section>

        @if ($spaces->hasPages())
            <nav class="places-pagination" aria-label="Places pages">
                <span class="places-pagination__summary">Page {{ $spaces->currentPage() }} of {{ max(1, $spaces->lastPage()) }}</span>
                <div class="places-pagination__pages">
                @if ($spaces->onFirstPage())
                    <span class="places-pagination__disabled places-pagination__previous" aria-disabled="true">← Previous</span>
                @else
                    <a class="places-pagination__previous" href="{{ $spaces->previousPageUrl() }}">← Previous</a>
                @endif

                @foreach ($spaces->getUrlRange(max(1, $spaces->currentPage() - 2), min($spaces->lastPage(), $spaces->currentPage() + 2)) as $page => $url)
                    @if ($page === $spaces->currentPage())
                        <span class="is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($spaces->hasMorePages())
                    <a class="places-pagination__next" href="{{ $spaces->nextPageUrl() }}">Next →</a>
                @else
                    <span class="places-pagination__disabled places-pagination__next" aria-disabled="true">Next →</span>
                @endif
                </div>
            </nav>
        @endif

        <button type="button" id="voice-search" class="places-voice-search" data-url="{{ route('spaces.search.voice') }}" data-csrf="{{ csrf_token() }}">ابحث بصوتك</button>
        <p id="voice-status" role="status" aria-live="polite"></p>
        <p id="voice-transcript" dir="auto"></p>

    </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('js/space-search-summary-audio.js') }}"></script>
    <script src="{{ asset('js/space-voice-search.js') }}" defer></script>
    <script src="{{ asset('js/space-card-gallery.js') }}" defer></script>
@endpush
