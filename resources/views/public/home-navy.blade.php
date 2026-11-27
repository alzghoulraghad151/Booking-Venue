@extends('layouts.users')

@section('title', 'Find your space')

@php
    use Illuminate\Support\Str;

    // Presentation helpers only: Cloudinary resizing and Arabic labels for the language toggle.
    $img = fn (?string $url, int $w) => $url && str_contains($url, 'res.cloudinary.com/') && ! preg_match('#/upload/[a-z]_#', $url)
        ? preg_replace('#/upload/#', '/upload/w_'.$w.',c_limit,q_auto,f_auto/', $url, 1)
        : $url;
    $cityAr = [
        'amman' => 'عمّان', 'irbid' => 'إربد', 'ajloun' => 'عجلون', 'jerash' => 'جرش', 'balqa' => 'البلقاء',
        'aqaba' => 'العقبة', 'madaba' => 'مادبا', 'zarqa' => 'الزرقاء', 'maan' => 'معان', 'karak' => 'الكرك',
        'salt' => 'السلط', 'mafraq' => 'المفرق', 'tafilah' => 'الطفيلة', 'dead sea' => 'البحر الميت', 'petra' => 'البتراء',
        'wadi rum' => 'وادي رم',
    ];
    $ar = fn (?string $city) => $cityAr[Str::lower(trim((string) $city))] ?? $city;
    $typeAr = [
        'corporate conference' => 'مؤتمرات الشركات', 'dinner' => 'عشاء العمل', 'networking' => 'لقاءات التواصل',
        'team building' => 'بناء الفريق', 'workshop / seminar' => 'ورش العمل والندوات',
    ];
    $tAr = fn (string $name) => $typeAr[Str::lower(trim($name))] ?? $name;

    $locations = collect($locations);
    $occasionCards = collect($occasionCards);
    // Hero photos: real, licensed photos stored in public/images/hero (see design-reference/IMAGE-CREDITS.md).
    $heroSlides = collect([
        ['file' => 'conference', 'label' => 'Conference hall', 'label_ar' => 'قاعة مؤتمرات'],
        ['file' => 'boardroom', 'label' => 'Boardroom', 'label_ar' => 'قاعة اجتماعات'],
        ['file' => 'gala', 'label' => 'Gala dinner', 'label_ar' => 'عشاء رسمي'],
        ['file' => 'wadi-rum', 'label' => 'Desert retreat, Wadi Rum', 'label_ar' => 'مخيم وادي رم'],
    ]);
    $cards = $latestPlaces->take(4);
    $favoriteIds = auth()->check() && method_exists(auth()->user(), 'favoriteSpaces')
        ? auth()->user()->favoriteSpaces()->whereIn('spaces.id', $cards->pluck('id'))->pluck('spaces.id')->all()
        : [];
    $totalSpaces = $locations->sum('space_count');

    // Event types often share one venue photo; give each tile a distinct real photo when that happens.
    $usedImages = [];
    $imagePool = $latestPlaces->flatMap(fn ($p) => $p->images->pluck('image_url'))
        ->merge($featuredVenues->flatMap(fn ($v) => $v->images->pluck('image_url')))
        ->unique()->values();
    $typeImages = $occasionCards->map(function (array $card) use (&$usedImages, $imagePool) {
        $url = $card['image_url'];
        if (in_array($url, $usedImages, true)) {
            $url = $imagePool->first(fn ($u) => ! in_array($u, $usedImages, true)) ?? $url;
        }
        $usedImages[] = $url;

        return $url;
    });
@endphp

@push('nav-tools')
    <button type="button" class="site-lang" id="nvh-lang" aria-label="التبديل إلى العربية" lang="ar">ع</button>
@endpush

@push('styles')
    <meta name="description" content="Find a business venue in Jordan. Explore event spaces by city, event type and guest count with Convene.">
    <link rel="stylesheet" href="{{ asset('css/convene-navy-home.css') }}?v={{ @filemtime(public_path('css/convene-navy-home.css')) }}">
@endpush

@section('content')
<div class="nvh" id="nvh">

    {{-- ================= HERO ================= --}}
    <section class="nvh-hero" id="top" aria-labelledby="nvh-title">
        <div class="nvh-sky" aria-hidden="true"></div>
        <div class="nvh-photos" data-parallax="photos" aria-hidden="true">
            @foreach ($heroSlides as $i => $slide)
                <figure class="{{ $i === 0 ? 'on' : '' }}"><img src="{{ asset('images/hero/'.$slide['file'].'.jpg') }}" alt="" @if ($i) loading="lazy" @endif></figure>
            @endforeach
        </div>
        <div class="nvh-fog" data-parallax="fog" aria-hidden="true">
            <i style="width:46%;height:60%;left:0;bottom:0;animation-duration:38s"></i>
            <i style="width:40%;height:70%;left:28%;bottom:-10%;animation-duration:44s;animation-delay:-8s"></i>
            <i style="width:50%;height:56%;left:58%;bottom:4%;animation-duration:52s;animation-delay:-20s"></i>
        </div>

        <div class="nvh-hero__inner">
            <div class="nvh-headline" data-parallax="headline">
                <h1 id="nvh-title" data-en="Book the right venue for your company&#39;s events in Jordan" data-ar="احجز مكان فعاليات شركتك في الأردن بسهولة">Book the right venue for your company's events in Jordan</h1>
                <p class="nvh-sub" data-en="Conference halls, workshop rooms and corporate event spaces in every city of the Kingdom." data-ar="قاعات مؤتمرات، ورش عمل، وفعاليات شركات في كل مدن المملكة.">Conference halls, workshop rooms and corporate event spaces in every city of the Kingdom.</p>
            </div>

            <div class="nvh-hero__row">
                <div class="nvh-circles" role="group" aria-label="Hero photos">
                    @foreach ($heroSlides as $i => $slide)
                        <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-slide="{{ $i }}" data-label="{{ $slide['label'] }}" data-label-ar="{{ $slide['label_ar'] }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" aria-label="{{ $slide['label'] }}" title="{{ $slide['label'] }}">
                            <img src="{{ asset('images/hero/'.$slide['file'].'-thumb.jpg') }}" alt="">
                        </button>
                    @endforeach
                </div>
                <span class="nvh-slide-label" id="nvh-slide-label">{{ $heroSlides[0]['label'] }}</span>
                <span class="nvh-count" dir="ltr" aria-hidden="true"><b id="nvh-cnt">01</b> / {{ str_pad((string) $heroSlides->count(), 2, '0', STR_PAD_LEFT) }}</span>
            </div>

            <div class="nvh-searchbox">
                <form id="landing-search" class="nvh-sbar" action="{{ route('spaces.search') }}" method="GET" aria-label="Search event spaces">
                    <label class="nvh-field nvh-field--kw">
                        <span class="nvh-ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></span>
                        <span class="nvh-fl">
                            <b data-en="Venue or space" data-ar="المكان أو القاعة">Venue or space</b>
                            <input id="landing-search-name" name="search" type="search" placeholder="Name or keyword" data-ph-ar="الاسم أو كلمة بحث" autocomplete="off">
                        </span>
                    </label>
                    <label class="nvh-field">
                        <span class="nvh-ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <span class="nvh-fl">
                            <b data-en="City" data-ar="المدينة">City</b>
                            <input id="landing-address" name="address" type="text" list="landing-locations" placeholder="All of Jordan" data-ph-ar="كل الأردن" autocomplete="off">
                            <datalist id="landing-locations">
                                @foreach ($locations as $location)
                                    <option value="{{ $location['address'] }}"></option>
                                @endforeach
                            </datalist>
                        </span>
                    </label>
                    <label class="nvh-field">
                        <span class="nvh-ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></span>
                        <span class="nvh-fl">
                            <b data-en="Event type" data-ar="نوع الفعالية">Event type</b>
                            <select name="event_type_id" id="landing-type">
                                <option value="" data-en="Any type" data-ar="أي نوع">Any type</option>
                                @foreach ($occasionCards as $card)
                                    <option value="{{ $card['id'] }}" data-en="{{ $card['name'] }}" data-ar="{{ $tAr($card['name']) }}">{{ $card['name'] }}</option>
                                @endforeach
                            </select>
                        </span>
                    </label>
                    <label class="nvh-field nvh-field--guests">
                        <span class="nvh-ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.4-5.5 6.5-5.5s5.7 1.9 6.5 5.5"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.6c2 .7 3.2 2.5 3.6 5.4"/></svg></span>
                        <span class="nvh-fl">
                            <b data-en="Guests" data-ar="الضيوف">Guests</b>
                            <input id="landing-capacity" name="capacity" type="number" min="1" placeholder="How many?" data-ph-ar="كم شخص؟">
                        </span>
                    </label>
                    <input type="hidden" name="lang" value="en" id="landing-lang">
                    <div class="nvh-row2">
                        <button type="button" class="nvh-mic" data-nv-voice aria-label="Search by voice" title="Search by voice">
                            <i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/></svg></i>
                            <span data-en="Voice" data-ar="بالصوت">Voice</span>
                        </button>
                        <button type="submit" class="nvh-go" aria-label="Search spaces"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><span data-en="Search" data-ar="ابحث">Search</span></button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- ================= SELECTED PLACES ================= --}}
    <section class="nvh-recs" id="recs" aria-labelledby="nvh-recs-title">
        <div class="nvh-wrap">
            <div class="nvh-sh nvh-rv">
                <div>
                    <h2 id="nvh-recs-title" data-en="Selected <em>places</em> for your event" data-ar="أماكن <em>مختارة</em> لفعاليتك">Selected <em>places</em> for your event</h2>
                    <p data-en="Newly added spaces, with real hourly prices." data-ar="مساحات أضيفت حديثًا بأسعار حقيقية بالساعة.">Newly added spaces, with real hourly prices.</p>
                </div>
                <a class="nvh-pill" href="{{ route('spaces.search') }}" data-en="See all" data-ar="عرض الكل">See all</a>
            </div>

            <div class="nvh-cards">
                @forelse ($cards as $i => $place)
                    @php($isFav = in_array($place->id, $favoriteIds, true))
                    <article class="nvh-card" style="transition-delay:{{ $i * 110 }}ms">
                        <a class="nvh-card__link" href="{{ route('spaces.show', $place) }}" aria-label="{{ $place->name }}">
                            <img src="{{ $img($place->images->first()?->image_url, 700) }}" alt="" loading="lazy">
                        </a>
                        @if ($place->hourly_rate !== null)
                            <span class="nvh-chip" dir="auto"><span data-en="From" data-ar="من">From</span> {{ rtrim(rtrim(number_format((float) $place->hourly_rate, 2), '0'), '.') }} <span data-en="JOD" data-ar="د.أ">JOD</span><small> / <span data-en="hr" data-ar="ساعة">hr</span></small></span>
                        @endif
                        @auth
                            <form class="place-card__favorite-form nvh-heart-form" action="{{ route('spaces.favorite.toggle', $place) }}" method="POST">
                                @csrf
                                <button class="nvh-heart place-card__favorite {{ $isFav ? 'is-active' : '' }}" type="submit" aria-pressed="{{ $isFav ? 'true' : 'false' }}" aria-label="{{ $isFav ? 'Remove from favorites' : 'Add to favorites' }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
                                </button>
                            </form>
                        @else
                            <a class="nvh-heart" href="{{ route('login') }}" aria-label="Sign in to save {{ $place->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
                            </a>
                        @endauth
                        <div class="nvh-card__meta">
                            <h3>{{ $place->name }}</h3>
                            <p>
                                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> {{ $place->venue->name }} · <span data-en="{{ $place->venue->address }}" data-ar="{{ $ar($place->venue->address) }}">{{ $place->venue->address }}</span></span>
                                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.6 3.4-5.5 6.5-5.5s5.7 1.9 6.5 5.5"/></svg> <span data-en="Up to" data-ar="حتى">Up to</span> {{ $place->capacity }} <span data-en="guests" data-ar="شخص">guests</span></span>
                            </p>
                            <a class="nvh-card__more" href="{{ route('spaces.show', $place) }}" data-en="View details" data-ar="عرض التفاصيل">View details</a>
                        </div>
                    </article>
                @empty
                    <p class="nvh-empty" data-en="No active spaces have been added yet." data-ar="لم تتم إضافة مساحات بعد.">No active spaces have been added yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= VENUE SHOWCASE ================= --}}
    @if ($featuredVenues->isNotEmpty())
        <section class="nvh-show" id="venues" aria-roledescription="carousel" aria-label="Venues">
            <div class="nvh-blur" id="nvh-blur" style="background-image:url('{{ $img($featuredVenues->first()->images->first()?->image_url, 400) }}')" aria-hidden="true"></div>
            <p class="nvh-tag" data-en="Venues across Jordan" data-ar="أماكن في كل الأردن">Venues across Jordan</p>
            <div class="nvh-frame" id="nvh-frame">
                <div class="nvh-slides" aria-hidden="true">
                    @foreach ($featuredVenues as $i => $venue)
                        <div class="{{ $i === 0 ? 'on' : '' }}" style="background-image:url('{{ $img($venue->images->first()?->image_url, 1800) }}')" data-blur="{{ $img($venue->images->first()?->image_url, 400) }}"></div>
                    @endforeach
                </div>
                <div class="nvh-shade" aria-hidden="true"></div>
                <div class="nvh-titles" aria-live="polite">
                    @foreach ($featuredVenues as $i => $venue)
                        <h3 class="{{ $i === 0 ? 'cur' : ($i === 1 ? 'nxt' : '') }}" data-i="{{ $i }}">{{ $venue->name }}</h3>
                    @endforeach
                </div>
                <div class="nvh-slmeta">
                    @foreach ($featuredVenues as $i => $venue)
                        <p class="{{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}">
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> <span data-en="{{ $venue->address }}" data-ar="{{ $ar($venue->address) }}">{{ $venue->address }}</span></span>
                            <span>{{ $venue->active_spaces_count }} <span data-en="{{ Str::plural('bookable space', $venue->active_spaces_count) }}" data-ar="مساحة قابلة للحجز">{{ Str::plural('bookable space', $venue->active_spaces_count) }}</span></span>
                        </p>
                    @endforeach
                </div>
                <a class="nvh-more" id="nvh-more" href="{{ route('spaces.search', ['venue_id' => $featuredVenues->first()->id]) }}">
                    <i aria-hidden="true"><svg class="nvh-flip" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></i>
                    <span data-en="Explore spaces" data-ar="استكشف المساحات">Explore spaces</span>
                </a>
                <div class="nvh-slnav">
                    <span id="nvh-si" dir="ltr">01</span>
                    <div class="nvh-bar" aria-hidden="true"><i id="nvh-bar" style="width:{{ 100 / $featuredVenues->count() }}%"></i></div>
                    <span dir="ltr">{{ str_pad((string) $featuredVenues->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    <button type="button" id="nvh-prev" aria-label="Previous venue"><svg class="nvh-flip" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 6l-6 6 6 6"/></svg></button>
                    <button type="button" id="nvh-next" aria-label="Next venue"><svg class="nvh-flip" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 6l6 6-6 6"/></svg></button>
                </div>
                <div class="nvh-dcur" id="nvh-dcur" aria-hidden="true"><span><b>‹</b><em data-en="DRAG" data-ar="اسحب">DRAG</em><b>›</b></span></div>
            </div>
            <div class="nvh-brandmk" aria-hidden="true"><i></i><span>Convene · Jordan</span></div>
            <div class="nvh-thumbs">
                @foreach ($featuredVenues as $i => $venue)
                    <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}" data-url="{{ route('spaces.search', ['venue_id' => $venue->id]) }}" aria-label="{{ $venue->name }}"><img src="{{ $img($venue->images->first()?->image_url, 120) }}" alt="" loading="lazy"></button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CITIES ================= --}}
    <section class="nvh-cities" id="cities" aria-labelledby="nvh-cities-title">
        <div class="nvh-wrap">
            <div class="nvh-sh nvh-rv">
                <div>
                    <h2 id="nvh-cities-title" data-en="{{ $locations->count() }} cities, <em>one</em> platform" data-ar="{{ $locations->count() }} مدن، <em>منصّة</em> واحدة">{{ $locations->count() }} cities, <em>one</em> platform</h2>
                    <p data-en="Pick a city to see every space you can book there." data-ar="اختر مدينة لترى كل المساحات المتاحة للحجز فيها.">Pick a city to see every space you can book there.</p>
                </div>
            </div>
            <div class="nvh-crow">
                @forelse ($locations as $i => $location)
                    <a class="nvh-city nvh-rv" style="transition-delay:{{ $i * 80 }}ms" href="{{ route('spaces.search', ['address' => $location['address']]) }}">
                        @php($cityKey = Str::slug($location['address']))
                        @if (is_file(public_path('images/cities/'.$cityKey.'.jpg')))
                            <span class="nvh-ph"><img src="{{ asset('images/cities/'.$cityKey.'.jpg') }}" alt="" loading="lazy"></span>
                        @else
                            <span class="nvh-ph nvh-ph--mono" aria-hidden="true"><span lang="ar">{{ $ar($location['address']) }}</span></span>
                        @endif
                        <b data-en="{{ $location['address'] }}" data-ar="{{ $ar($location['address']) }}">{{ $location['address'] }}</b>
                        <small>{{ $location['space_count'] }} <span data-en="{{ Str::plural('space', $location['space_count']) }}" data-ar="مساحة">{{ Str::plural('space', $location['space_count']) }}</span></small>
                    </a>
                @empty
                    <p class="nvh-empty" data-en="No venue addresses are available yet." data-ar="لا توجد مدن متاحة بعد.">No venue addresses are available yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= EVENT TYPES ================= --}}
    <section class="nvh-types" id="event-types" aria-labelledby="nvh-types-title">
        <div class="nvh-wrap">
            <div class="nvh-sh nvh-rv">
                <div>
                    <h2 id="nvh-types-title" data-en="A space for <em>every</em> business occasion" data-ar="لكل مناسبة عمل <em>مكانها</em>">A space for <em>every</em> business occasion</h2>
                    <p data-en="Browse by the kind of event you are planning." data-ar="تصفّح حسب نوع الفعالية التي تخطط لها.">Browse by the kind of event you are planning.</p>
                </div>
            </div>
            <div class="nvh-tgrid">
                @forelse ($occasionCards as $i => $card)
                    <a class="nvh-tile" style="transition-delay:{{ $i * 90 }}ms" href="{{ route('spaces.search', ['event_type_id' => $card['id']]) }}">
                        <img src="{{ $img($typeImages[$i] ?? $card['image_url'], $i ? 800 : 1200) }}" alt="" loading="lazy">
                        <span class="nvh-tile__in">
                            <span>
                                <b data-en="{{ $card['name'] }}" data-ar="{{ $tAr($card['name']) }}">{{ $card['name'] }}</b>
                                @if ($card['description'])
                                    <small>{{ Str::limit($card['description'], 80) }}</small>
                                @endif
                            </span>
                            <span class="nvh-ar" aria-hidden="true"><svg class="nvh-flip" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                        </span>
                    </a>
                @empty
                    <p class="nvh-empty" data-en="No event types have available spaces yet." data-ar="لا توجد أنواع فعاليات متاحة بعد.">No event types have available spaces yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= SHEMAGH BAND ================= --}}
    <div class="nvh-band" aria-hidden="true">
        <div class="nvh-mq">
            @for ($r = 0; $r < 4; $r++)
                @foreach ($occasionCards as $card)
                    <span data-en="{{ $card['name'] }}" data-ar="{{ $tAr($card['name']) }}">{{ $card['name'] }}</span>
                @endforeach
                <span data-en="Jordanian hospitality" data-ar="ضيافة أردنية">Jordanian hospitality</span>
            @endfor
        </div>
    </div>

    {{-- ================= CTA ================= --}}
    <section class="nvh-cta" id="cta" aria-labelledby="nvh-cta-title">
        <div class="nvh-ctaimg" data-parallax="cta" style="background-image:url('{{ $img($featuredVenues->last()?->images->first()?->image_url, 1800) }}')" aria-hidden="true"></div>
        <div class="nvh-fog nvh-fog--top" aria-hidden="true"><i style="width:50%;height:70%;left:5%;bottom:0;animation-duration:40s"></i><i style="width:46%;height:80%;left:45%;bottom:-10%;animation-duration:50s;animation-delay:-12s"></i><i style="width:40%;height:60%;left:80%;bottom:5%;animation-duration:44s"></i></div>
        <div class="nvh-wrap">
            <h2 id="nvh-cta-title" class="nvh-rv" data-en="Ready to book<br>your next event?" data-ar="جاهز تحجز<br>مكان فعاليتك القادمة؟">Ready to book<br>your next event?</h2>
            <p class="nvh-rv" data-en="Compare real venues, check availability and pay securely, all in one place." data-ar="قارن بين أماكن حقيقية، تحقّق من التوفر، وادفع بأمان. كل هذا في مكان واحد.">Compare real venues, check availability and pay securely, all in one place.</p>
            <div class="nvh-ctarow nvh-rv">
                <a class="nvh-pill nvh-pill--dark" href="{{ route('spaces.search') }}" data-en="Browse all spaces" data-ar="تصفّح كل المساحات">Browse all spaces</a>
                @guest
                    @if (Route::has('register'))
                        <a class="nvh-pill" href="{{ route('register') }}" data-en="Create an account" data-ar="أنشئ حسابًا">Create an account</a>
                    @endif
                @endguest
            </div>
            <dl class="nvh-facts nvh-rv">
                <div><dt data-en="bookable spaces" data-ar="مساحة قابلة للحجز">bookable spaces</dt><dd data-count="{{ $totalSpaces }}">{{ $totalSpaces }}</dd></div>
                <div><dt data-en="cities" data-ar="مدن">cities</dt><dd data-count="{{ $locations->count() }}">{{ $locations->count() }}</dd></div>
                <div><dt data-en="event types" data-ar="أنواع فعاليات">event types</dt><dd data-count="{{ $occasionCards->count() }}">{{ $occasionCards->count() }}</dd></div>
            </dl>
        </div>
    </section>

    {{-- ================= NASHMI (always on, wired to space-voice-search.js) ================= --}}
    <div class="nvh-nash" id="nvh-nash">
        <button type="button" id="voice-search" form="landing-search" class="nvh-face"
                data-url="{{ route('spaces.search.voice') }}" data-csrf="{{ csrf_token() }}" aria-label="Talk to Nashmi, the voice search assistant">
            <span class="nvh-face__img" aria-hidden="true"><span lang="ar">نشمي</span></span>
            <span class="nvh-face__m" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/></svg></span>
            <span class="nvh-sr" data-voice-label>Talk to Nashmi</span>
        </button>
        <div class="nvh-bub" id="nvh-bub">
            <b data-en="Nashmi · your event assistant" data-ar="نشمي · مساعد فعالياتك">Nashmi · your event assistant</b>
            <span id="nvh-tip" dir="auto">Tell me the city and the number of guests, and I will find the space.</span>
            <span id="voice-status" role="status" dir="auto"></span>
            <span id="voice-transcript" dir="auto"></span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/space-voice-search.js') }}" defer></script>
    <script src="{{ asset('js/space-favorites.js') }}" defer></script>
    <script src="{{ asset('js/convene-navy-home.js') }}?v={{ @filemtime(public_path('js/convene-navy-home.js')) }}" defer></script>
@endpush
