@extends('layouts.users')

@section('title', 'Find your space')

@push('styles')
    <meta name="description" content="Find a venue for your next gathering. Explore event spaces by address and guest count with Convene.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Raleway:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@300..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/convene-landing.css') }}">
@endpush

@section('content')
<div class="convene-landing">
    <section class="home-hero" aria-labelledby="home-hero-title">
        <div class="home-container home-hero__inner">
            <div class="home-hero__copy">
                <p class="home-eyebrow">Curated event spaces</p>
                <h1 id="home-hero-title">Find the right space for your next event.</h1>
                <p class="home-hero__intro">Discover private boardrooms, architectural dining salons, and gathering spaces across Jordan.</p>
                <a class="home-button home-button--light" href="#explore-locations">Explore spaces <span aria-hidden="true">→</span></a>
            </div>

            <form id="landing-search" class="home-search" action="{{ route('spaces.search') }}" method="GET" aria-label="Search event spaces">
                <div class="home-search__location">
                    <label for="landing-address">Location / Address</label>
                    <div class="home-address">
                        <input id="landing-address" name="address" type="text" list="landing-locations" placeholder="Choose an address" autocomplete="street-address" required>
                        <datalist id="landing-locations">
                            @foreach ($locations as $location)
                                <option value="{{ $location['address'] }}"></option>
                            @endforeach
                        </datalist>
                        <span class="home-address__chevron" aria-hidden="true"></span>
                        <div class="home-address__menu" id="landing-address-menu" hidden>
                            <ul role="listbox" id="landing-address-options" aria-label="Addresses"></ul>
                            <p role="status" hidden></p>
                        </div>
                    </div>
                </div>
                <div class="home-search__guests">
                    <label for="landing-capacity">Number of guests</label>
                    <input id="landing-capacity" name="capacity" type="number" min="1" placeholder="Any group size">
                </div>
                <button class="home-button home-button--dark" type="submit"><span class="material-symbols-outlined" aria-hidden="true">search</span> Search spaces</button>
            </form>
        </div>
    </section>

    <section class="home-section" id="explore-locations" aria-labelledby="home-locations-title">
        <div class="home-section__heading home-section__heading--split">
            <div>
                <p class="home-eyebrow home-eyebrow--accent">Explore by location</p>
                <h2 id="home-locations-title">Find a space where it happens.</h2>
                <p>Browse available event spaces by their venue address.</p>
            </div>
            <a class="home-text-link" href="{{ route('spaces.search') }}">View all spaces <span aria-hidden="true">→</span></a>
        </div>

        <div class="home-carousel" data-home-carousel aria-label="Locations">
            <button class="home-carousel__arrow home-carousel__arrow--previous" type="button" data-carousel-previous aria-label="Show previous locations">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="home-carousel__viewport" data-carousel-viewport tabindex="0">
                <div class="home-carousel__track" data-carousel-track>
                    @forelse ($locations as $location)
                        <a class="home-location-card home-carousel__item" href="{{ route('spaces.search', ['address' => $location['address']]) }}">
                            <div class="home-location-card__image"><img src="{{ $location['image_url'] }}" alt="" loading="lazy"></div>
                            <div class="home-location-card__caption">
                                <h3>{{ $location['address'] }}</h3>
                                <span>{{ $location['space_count'] }} {{ \Illuminate\Support\Str::plural('space', $location['space_count']) }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="home-empty">No venue addresses are available yet.</p>
                    @endforelse
                </div>
            </div>
            <button class="home-carousel__arrow home-carousel__arrow--next" type="button" data-carousel-next aria-label="Show more locations">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </section>

    <section class="home-band" aria-labelledby="home-venues-title">
        <div class="home-container">
            <div class="home-section__heading">
                <p class="home-eyebrow home-eyebrow--accent">Partner establishments</p>
                <h2 id="home-venues-title">Venues to explore</h2>
                <p>Each venue brings together distinct spaces for different occasions.</p>
            </div>

            <div class="home-carousel" data-home-carousel aria-label="Venues">
                <button class="home-carousel__arrow home-carousel__arrow--previous" type="button" data-carousel-previous aria-label="Show previous venues">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <div class="home-carousel__viewport" data-carousel-viewport tabindex="0">
                    <div class="home-carousel__track" data-carousel-track>
                        @forelse ($featuredVenues as $venue)
                            <article class="home-space-card home-carousel__item">
                                <a class="home-space-card__image" href="{{ route('spaces.search', ['venue_id' => $venue->id]) }}" aria-label="Browse spaces at {{ $venue->name }}">
                                    <img src="{{ $venue->images->first()?->image_url }}" alt="{{ $venue->name }}" loading="lazy">
                                </a>
                                <div class="home-space-card__body">
                                    <p class="home-space-card__venue">{{ $venue->address }}</p>
                                    <h3><a href="{{ route('spaces.search', ['venue_id' => $venue->id]) }}">{{ $venue->name }}</a></h3>
                                    @if ($venue->description)
                                        <p class="home-space-card__description">{{ \Illuminate\Support\Str::limit($venue->description, 120) }}</p>
                                    @endif
                                    <div class="home-space-card__footer">
                                        <span>{{ $venue->active_spaces_count }} {{ \Illuminate\Support\Str::plural('active space', $venue->active_spaces_count) }}</span>
                                        <strong><a href="{{ route('spaces.search', ['venue_id' => $venue->id]) }}">Explore spaces →</a></strong>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="home-empty">No approved venues are available yet.</p>
                        @endforelse
                    </div>
                </div>
                <button class="home-carousel__arrow home-carousel__arrow--next" type="button" data-carousel-next aria-label="Show more venues">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>
    </section>

    <section class="home-section home-arrivals" aria-labelledby="home-latest-title" data-home-arrivals>
        <div class="home-section__heading home-section__heading--split">
            <div>
                <p class="home-eyebrow home-eyebrow--accent">Just added</p>
                <h2 id="home-latest-title">New spaces to discover</h2>
            </div>
            <a class="home-text-link" href="{{ route('spaces.search') }}">Explore all spaces <span aria-hidden="true">→</span></a>
        </div>

        <div class="home-arrivals__viewport">
            <div class="home-arrivals__track" data-arrivals-track>
                <div class="home-arrivals__group" data-arrivals-group>
                    @forelse ($latestPlaces as $place)
                        <article class="home-space-card">
                            <a class="home-space-card__image" href="{{ route('spaces.show', $place) }}" aria-label="View {{ $place->name }}">
                                <img src="{{ $place->images->first()?->image_url }}" alt="{{ $place->name }}" loading="lazy">
                            </a>
                            <div class="home-space-card__body">
                                <p class="home-space-card__venue">{{ $place->venue->name }} · {{ $place->venue->address }}</p>
                                <h3><a href="{{ route('spaces.show', $place) }}">{{ $place->name }}</a></h3>
                                @if ($place->description)
                                    <p class="home-space-card__description">{{ \Illuminate\Support\Str::limit($place->description, 120) }}</p>
                                @endif
                                <div class="home-space-card__footer">
                                    <span>Up to {{ $place->capacity }} {{ \Illuminate\Support\Str::plural('guest', $place->capacity) }}</span>
                                    @if ($place->hourly_rate !== null)
                                        <strong>{{ number_format((float) $place->hourly_rate, 2) }} JOD / hr</strong>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="home-empty">No active spaces have been added yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="home-occasions" aria-labelledby="home-occasions-title">
        <div class="home-container home-occasions__inner">
            <div class="home-occasions__intro">
                <p class="home-eyebrow home-eyebrow--brass">Format and purpose</p>
                <h2 id="home-occasions-title">Spaces for every kind of gathering.</h2>
                <p>Choose the occasion and discover a setting that fits your plans.</p>
            </div>
            <div class="home-occasion-grid">
                @forelse ($occasionCards as $card)
                    @php($icon = match (\Illuminate\Support\Str::lower($card['name'])) {
                        'dinner' => 'restaurant',
                        'networking' => 'hub',
                        'workshop / seminar' => 'co_present',
                        'corporate conference' => 'podium',
                        'team building' => 'diversity_3',
                        default => 'celebration',
                    })
                    <a class="home-occasion-card" href="{{ route('spaces.search', ['event_type_id' => $card['id']]) }}">
                        <span class="home-occasion-card__icon material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>
                        <h3>{{ $card['name'] }}</h3>
                        @if ($card['description'])
                            <p>{{ $card['description'] }}</p>
                        @endif
                    </a>
                @empty
                    <p class="home-empty">No event types have available spaces yet.</p>
                @endforelse
            </div>
        </div>
    </section>
    <div class="home-voice-widget" data-home-voice-widget lang="ar" dir="rtl">
        <div class="home-voice-widget__intro" data-voice-intro hidden>
            <button class="home-voice-widget__dismiss" type="button" data-dismiss-voice-intro aria-label="إغلاق التعريف">×</button>
            <p class="home-voice-widget__title">مرحباً، أنا مساعد البحث الصوتي</p>
            <p>اضغط واحكي وين بدك تحجز ولكم شخص.</p>
        </div>
        <div class="home-voice-widget__feedback">
            <p id="voice-status" role="status" dir="auto"></p>
            <p id="voice-transcript" dir="auto"></p>
        </div>
        <button type="button" id="voice-search" form="landing-search" class="home-voice-widget__button"
                data-url="{{ route('spaces.search.voice') }}" data-csrf="{{ csrf_token() }}">
            <svg class="home-voice-widget__mic" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                <rect x="9" y="2" width="6" height="12" rx="3"/>
                <path d="M5 11v1a7 7 0 0 0 14 0v-1M12 19v3m-3 0h6"/>
            </svg>
            <svg class="home-voice-widget__stop" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <rect x="6" y="6" width="12" height="12" rx="2"/>
            </svg>
            <span class="home-voice-widget__label" data-voice-label>ابدأ البحث بصوتك</span>
        </button>
        <p class="home-voice-widget__hint" aria-hidden="true">ابحث عن مساحة بصوتك</p>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/home-address-menu.js') }}" defer></script>
    <script src="{{ asset('js/space-voice-search.js') }}" defer></script>
    <script src="{{ asset('js/home-carousels.js') }}" defer></script>
@endpush
