@extends('layouts.users')

@section('title', 'Find your space')

@push('styles')
    <meta name="description" content="Find a venue for your next gathering. Explore event spaces by address and guest count with Convene.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Sans+Arabic:wght@400;600&family=Material+Symbols+Outlined:wght,FILL@300..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/convene-home.css') }}">
@endpush

@section('content')
<div class="convene-landing cv" data-state="idle">
    {{-- HERO: contained video card with search, live stats and interactive location stack --}}
    <section class="home-hero" aria-labelledby="home-hero-title" data-hero>
        <div class="cv-video" aria-hidden="true">
            <video autoplay muted loop playsinline preload="metadata" poster="{{ asset('images/convene-hero-poster.jpg') }}">
                <source src="{{ asset('videos/convene-hero.mp4') }}" type="video/mp4">
            </video>
        </div>
        <div class="cv-hero__grid">
            <div class="home-hero__copy">
                <p class="cv-kicker">Event venues for teams and companies</p>
                <h1 id="home-hero-title">Find the right room for your next event.</h1>
                <p class="home-hero__intro">Private boardrooms, dining salons and gathering spaces across Jordan.</p>
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
                <dl class="cv-stats">
                    <div><dt>Event spaces</dt><dd data-count="{{ collect($locations)->sum('space_count') }}">{{ collect($locations)->sum('space_count') }}</dd></div>
                    <div><dt>Locations</dt><dd data-count="{{ count($locations) }}">{{ count($locations) }}</dd></div>
                    <div><dt>Partner venues</dt><dd data-count="{{ count($featuredVenues) }}">{{ count($featuredVenues) }}</dd></div>
                </dl>
            </div>
            <div class="cv-hero__visual">
                <div class="cv-stack" data-stack>
                    @foreach (collect($locations)->take(3) as $i => $loc)
                        <a class="cv-stack__card" style="--i:{{ $i }}" href="{{ route('spaces.search', ['address' => $loc['address']]) }}">
                            <img src="{{ $loc['image_url'] }}" alt="" loading="lazy">
                            <span><b>{{ $loc['address'] }}</b><em>{{ $loc['space_count'] }} {{ \Illuminate\Support\Str::plural('space', $loc['space_count']) }}</em></span>
                        </a>
                    @endforeach
                </div>
                <div class="cv-chips" aria-label="Browse by occasion">
                    @foreach (collect($occasionCards)->take(4) as $i => $card)
                        <a class="cv-chip" style="--c:{{ $i }}" href="{{ route('spaces.search', ['event_type_id' => $card['id']]) }}">{{ $card['name'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Scroll-scrubbed statement --}}
    <section class="cv-statement" aria-label="Why Convene">
        <p data-scrub>Every great meeting starts with the right room. Tell us the place and the headcount, and we show you only the spaces that fit.</p>
    </section>

    <section class="home-section" id="explore-locations" aria-labelledby="home-locations-title">
        <div class="home-section__heading home-section__heading--split">
            <div>
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
                            <article class="home-space-card home-carousel__item home-venue-card">
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
            <div><h2 id="home-latest-title">New spaces to discover</h2></div>
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

    {{-- Occasions as an interactive index instead of cards --}}
    <section class="home-occasions" aria-labelledby="home-occasions-title">
        <div class="home-container home-occasions__inner">
            <div class="home-occasions__intro">
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
                        <span class="home-occasion-card__go" aria-hidden="true">→</span>
                    </a>
                @empty
                    <p class="home-empty">No event types have available spaces yet.</p>
                @endforelse
            </div>
        </div>
    </section>
<div class="cv-concierge">
            <div class="cv-bubble" data-bubble aria-hidden="true"><span data-bubble-text>Tell me about your next event, and I’ll find the perfect venue.</span></div>
            <div class="home-voice-widget" data-home-voice-widget lang="ar" dir="rtl">
                <div class="home-voice-widget__intro" data-voice-intro hidden>
                    <button class="home-voice-widget__dismiss" type="button" data-dismiss-voice-intro aria-label="إغلاق التعريف">×</button>
                    <p class="home-voice-widget__title">مرحباً، أنا مساعد البحث الصوتي</p>
                    <p>اضغط واحكي وين بدك تحجز ولكم شخص.</p>
                </div>
                <button type="button" id="voice-search" form="landing-search" class="home-voice-widget__button cv-avatar"
                        data-url="{{ route('spaces.search.voice') }}" data-csrf="{{ csrf_token() }}">
                    <span class="cv-avatar__stage" aria-hidden="true">
                        <span class="cv-ring"></span><span class="cv-ring"></span><span class="cv-ring"></span>
                        <svg class="cv-avatar__svg" viewBox="0 0 240 270">
                            <defs>
                                <pattern id="cvShm" width="12" height="12" patternUnits="userSpaceOnUse"><rect width="12" height="12" fill="#F6F2EA"/><rect width="6" height="6" fill="#B3202A"/><rect x="6" y="6" width="6" height="6" fill="#B3202A"/><path d="M0 0H12M0 6H12" stroke="#7D1119" stroke-opacity=".25" stroke-width=".6"/></pattern>
                                <linearGradient id="cvShade" x1="0" x2="1"><stop offset="0" stop-opacity=".4"/><stop offset=".3" stop-opacity="0"/><stop offset=".7" stop-opacity="0"/><stop offset="1" stop-opacity=".4"/></linearGradient>
                                <linearGradient id="cvMetal" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4A55C7"/><stop offset="1" stop-color="#1B2160"/></linearGradient>
                            </defs>
                            <path d="M14 270C14 214 58 196 120 196S226 214 226 270Z" fill="#171B4D"/>
                            <circle cx="120" cy="258" r="5" fill="#C9A45C"/>
                            <path d="M50 124C50 58 84 26 120 26S190 58 190 124L220 224C176 246 64 246 20 224Z" fill="url(#cvShm)"/>
                            <path d="M50 124C50 58 84 26 120 26S190 58 190 124L220 224C176 246 64 246 20 224Z" fill="url(#cvShade)"/>
                            <path d="M72 60C58 110 54 160 38 226M168 60C182 110 186 160 202 226M96 36C84 90 80 140 70 236M144 36C156 90 160 140 170 236" fill="none" stroke="#000" stroke-opacity=".16" stroke-width="2.5"/>
                            <path d="M75 60C61 110 57 160 41 226M171 60C185 110 189 160 205 226" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="1.5"/>
                            <path d="M20 224C64 246 176 246 220 224" fill="none" stroke="#B3202A" stroke-width="5" stroke-dasharray="1.5 3.5"/>
                            <rect x="70" y="66" width="100" height="108" rx="40" fill="#000" fill-opacity=".3"/>
                            <rect x="76" y="70" width="88" height="100" rx="36" fill="url(#cvMetal)" stroke="#C9A45C" stroke-width="2"/>
                            <rect x="84" y="94" width="72" height="38" rx="19" fill="#0B0E24"/>
                            <g class="cv-eyes"><rect class="cv-eye" x="98" y="104" width="14" height="18" rx="7"/><rect class="cv-eye" x="128" y="104" width="14" height="18" rx="7"/></g>
                            <path class="cv-mouth" d="M104 150Q120 158 136 150" fill="none" stroke="#6FE3C1" stroke-width="3" stroke-linecap="round"/>
                            <rect x="64" y="104" width="7" height="26" rx="3.5" fill="#C9A45C"/><rect x="169" y="104" width="7" height="26" rx="3.5" fill="#C9A45C"/>
                            <ellipse cx="120" cy="50" rx="66" ry="11" fill="none" stroke="#111" stroke-width="7"/>
                            <ellipse cx="120" cy="63" rx="70" ry="12" fill="none" stroke="#111" stroke-width="7"/>
                            <path d="M70 46Q120 38 170 46M66 59Q120 51 174 59" fill="none" stroke="#fff" stroke-opacity=".25" stroke-width="1.5"/>
                        </svg>
                        <span class="cv-think"><i></i><i></i><i></i></span>
                    </span>
                    <span class="cv-waves" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></span>
                    <span class="home-voice-widget__label cv-avatar__label" data-voice-label>ابدأ البحث بصوتك</span>
                </button>
                <div class="home-voice-widget__feedback">
                    <p id="voice-status" role="status" dir="auto"></p>
                    <p id="voice-transcript" dir="auto"></p>
                </div>
            </div>
        </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/home-address-menu.js') }}" defer></script>
    <script src="{{ asset('js/space-voice-search.js') }}" defer></script>
    <script src="{{ asset('js/home-carousels.js') }}" defer></script>
    <script src="{{ asset('js/home-experience.js') }}" defer></script>
@endpush
