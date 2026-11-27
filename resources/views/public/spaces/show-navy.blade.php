@extends('layouts.users')

@section('title', $space->name)

{{-- Navy redesign of public/spaces/show.blade.php. That file has uncommitted edits by the owner,
     so this parallel view carries the same content (including those edits) in the new layout.
     PublicSpaceDetailsController renders this view; switch it back to 'public.spaces.show' to undo.
     The booking form keeps every field name, id, value and JS hook of the original, so it submits
     to bookings.create exactly as before. --}}

@php
    $cl = fn (?string $url, int $w) => $url && str_contains($url, 'res.cloudinary.com/') && ! preg_match('#/upload/[a-z]_#', $url)
        ? preg_replace('#/upload/#', '/upload/w_'.$w.',c_limit,q_auto,f_auto/', $url, 1)
        : $url;
    $photos = $images->values();

    // Short layout titles: the layout type (Theatre, Banquet…) when the name contains one, otherwise the name without the space and venue names.
    $layoutKinds = [
        'theatre' => 'Theatre', 'theater' => 'Theatre', 'banquet' => 'Banquet', 'classroom' => 'Classroom',
        'boardroom' => 'Boardroom', 'u-shape' => 'U-shape', 'u shape' => 'U-shape', 'cabaret' => 'Cabaret',
        'reception' => 'Reception', 'cocktail' => 'Cocktail', 'hollow square' => 'Hollow square', 'workshop' => 'Workshop',
    ];
    $layoutKind = function (string $name) use ($layoutKinds): ?string {
        $lower = Str::lower($name);
        foreach ($layoutKinds as $needle => $label) {
            if (str_contains($lower, $needle)) {
                return $label;
            }
        }

        return null;
    };
    $layoutTitle = function (string $name) use ($layoutKind, $space, $venue): string {
        $short = trim(Str::of($name)->replaceFirst($space->name, '')->replaceFirst($venue->name, '')->toString(), " -–·,");

        return $layoutKind($name) ?? ($short !== '' ? $short : $name);
    };
    $icons = [
        'Theatre' => '<path d="M4 18h16M5 14h2m3 0h4m3 0h2M5 10h2m3 0h4m3 0h2"/><rect x="7" y="3" width="10" height="3" rx="1"/>',
        'Banquet' => '<circle cx="7" cy="8" r="3"/><circle cx="17" cy="8" r="3"/><circle cx="7" cy="17" r="3"/><circle cx="17" cy="17" r="3"/>',
        'Classroom' => '<rect x="4" y="7" width="7" height="3" rx="1"/><rect x="13" y="7" width="7" height="3" rx="1"/><rect x="4" y="14" width="7" height="3" rx="1"/><rect x="13" y="14" width="7" height="3" rx="1"/>',
        'Boardroom' => '<rect x="7" y="5" width="10" height="14" rx="2"/><path d="M4 8v2M4 14v2M20 8v2M20 14v2"/>',
        'U-shape' => '<path d="M6 5v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5"/><path d="M10 5v9h4V5"/>',
        'Cabaret' => '<path d="M4 12a5 5 0 0 1 10 0"/><path d="M10 19a5 5 0 0 1 10 0"/>',
        'Reception' => '<path d="M8 3h4l-1 6a2 2 0 0 1-2 0z"/><path d="M10 9v8M8 21h4"/><path d="M14 6h4l-1 6a2 2 0 0 1-2 0z"/><path d="M16 12v6M14 21h4"/>',
    ];
    $icons['Cocktail'] = $icons['Reception'];
    $icons['Hollow square'] = $icons['U-shape'];
    $icons['Workshop'] = $icons['Classroom'];
    $defaultIcon = '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M4 12h16M12 4v16"/>';
    $rate = $space->hourly_rate !== null ? (float) $space->hourly_rate : null;
    $prefill = fn (string $field) => old($field, is_string(request()->query($field)) ? request()->query($field) : '');
@endphp

@section('content')
    <div class="nvp nvp-space space-details-page">
        <section class="nvp-gallery nvp-gallery--{{ min(5, max(1, $photos->count())) }}" aria-label="Space photos">
            @forelse ($photos->take(5) as $image)
                <figure class="nvp-gallery__photo">
                    <img src="{{ $cl($image->image_url, $loop->first ? 1800 : 900) }}"
                         alt="{{ $space->name }} — photo {{ $loop->iteration }}"
                         @unless ($loop->first) loading="lazy" @endunless>
                    @if ($loop->last && $photos->count() > 5)
                        <span class="nvp-gallery__more">+{{ $photos->count() - 5 }} photos</span>
                    @endif
                </figure>
            @empty
                <p class="nvp-gallery__empty">No photos are available for this space.</p>
            @endforelse
        </section>

        <header class="nvp-band nvp-band--space">
            <div class="nvp-wrap nvp-space__head">
                <div>
                    <p class="nvp-eyebrow">{{ $venue->name }}@if (filled($venue->address)) · {{ $venue->address }}@endif</p>
                    <h1>{{ $space->name }}</h1>
                    <ul class="nvp-facts">
                        <li><b>{{ $space->capacity }}</b> guests</li>
                        @if ($rate !== null)
                            <li><b>{{ number_format($rate, 2) }}</b> JOD / hour</li>
                        @endif
                        @if ($reviews->isNotEmpty())
                            <li><a href="#reviews-heading"><b>★ {{ number_format((float) $reviews->avg('rating'), 1) }}</b> · {{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }}</a></li>
                        @endif
                    </ul>
                </div>
                <a class="nvp-pill nvp-pill--light space-details__book-link" href="#space-booking-form" data-nvb-open>Book this space</a>
            </div>
        </header>

        {{-- ================= BOOKING FLOW ================= --}}
        <section class="nvb" id="book" aria-labelledby="nvb-title" data-rate="{{ $rate ?? '' }}">
            <div class="nvb-backdrop" data-nvb-close></div>
            <form id="space-booking-form" class="space-booking-options nvb-form"
                  method="GET"
                  action="{{ route('bookings.create', ['space' => $space]) }}">
                <header class="nvb-head">
                    <div>
                        <p class="nvb-eyebrow">Book this space</p>
                        <h2 class="space-booking-options__heading" id="nvb-title">{{ $space->name }}</h2>
                    </div>
                    <p class="nvb-head__meta">
                        @if ($rate !== null)<b>{{ number_format($rate, 2) }} JOD</b> / hour · @endif up to <b>{{ $space->capacity }}</b> guests
                    </p>
                    <button type="button" class="nvb-close" data-nvb-close aria-label="Close booking">×</button>
                </header>

                @error('guest_count')
                    <p class="nvb-alert" role="alert">{{ $message }}</p>
                @enderror

                <ol class="nvb-steps">
                    {{-- 1. Event type --}}
                    <li class="nvb-step" data-nvb-step="type">
                        <span class="nvb-step__num" aria-hidden="true">1</span>
                        <section class="nvb-step__body" aria-labelledby="event-type-heading">
                            <h3 id="event-type-heading">Event type <i class="nvb-req" title="Required"><span class="nvp-sr">required</span></i></h3>
                            <label class="nvp-sr" for="event_type_id">Event type</label>
                            <select id="event_type_id" name="event_type_id" required>
                                <option value="">Select</option>
                                @foreach ($eventTypes as $eventType)
                                    <option value="{{ $eventType->id }}"
                                            @selected((string) old('event_type_id') === (string) $eventType->id)>
                                        {{ $eventType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('event_type_id')
                                <p class="nvb-alert" role="alert">{{ $message }}</p>
                            @enderror
                        </section>
                    </li>

                    {{-- 2. Layout --}}
                    <li class="nvb-step" data-nvb-step="layout">
                        <span class="nvb-step__num" aria-hidden="true">2</span>
                        <section class="nvb-step__body" aria-labelledby="layouts-heading">
                            <h3 id="layouts-heading">Layout <i class="nvb-req" title="Required"><span class="nvp-sr">required</span></i></h3>

                            @if ($layouts->isEmpty())
                                <p class="nvb-hint">No layouts are available for this space.</p>
                            @else
                                <p class="nvb-hint" data-layout-status role="status">Choose an event type first.</p>
                                <fieldset class="layout-options" data-layout-options hidden>
                                    <legend class="nvp-sr">Choose one layout</legend>
                                    <div class="layout-options__grid nvb-tiles">
                                        @foreach ($layouts as $layout)
                                            @php($kind = $layoutKind($layout->name))
                                            <label class="layout-option nvb-tile"
                                                   data-layout-event-types="{{ implode(' ', $layout->eventTypes->modelKeys()) }}"
                                                   hidden>
                                                <input type="radio"
                                                       name="space_layout_id"
                                                       value="{{ $layout->id }}"
                                                       disabled
                                                       @checked((string) old('space_layout_id') === (string) $layout->id)
                                                       required>

                                                <span class="layout-option__card" data-nvb-label="{{ $layoutTitle($layout->name) }}">
                                                    <svg class="nvb-tile__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">{!! $icons[$kind] ?? $defaultIcon !!}</svg>
                                                    <strong>{{ $layoutTitle($layout->name) }}</strong>
                                                    <span class="nvb-tile__cap"><b>{{ $layout->capacity }}</b> guests</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endif

                            @error('space_layout_id')
                                <p class="nvb-alert" role="alert">{{ $message }}</p>
                            @enderror
                        </section>
                    </li>

                    {{-- 3. Package --}}
                    <li class="nvb-step" data-nvb-step="package">
                        <span class="nvb-step__num" aria-hidden="true">3</span>
                        <section class="nvb-step__body" aria-labelledby="packages-heading">
                            <h3 id="packages-heading">Package <small>optional</small></h3>

                            <p class="nvb-hint" data-package-status role="status">Choose an event type first.</p>

                            @if ($packages->isNotEmpty())
                                <fieldset class="layout-options">
                                    <legend class="nvp-sr">Choose a package</legend>

                                    <div class="nvb-pkgs">
                                        <label class="no-package-option nvb-pkg nvb-pkg--none">
                                            <input type="radio"
                                                   name="package_choice"
                                                   value=""
                                                   @checked(! $packages->contains('id', old('package_id')))>
                                            <span class="nvb-pkg__title">No package</span>
                                            <span class="nvb-pkg__desc">Space only, billed by the hour</span>
                                        </label>

                                        <div class="package-options__grid">
                                            @foreach ($packages as $package)
                                                <article class="package-option nvb-pkg"
                                                         data-event-type-ids="{{ implode(' ', $package->eventTypes->modelKeys()) }}"
                                                         data-price="{{ (float) $package->price }}"
                                                         data-name="{{ $package->name }}"
                                                         hidden>
                                                    <label class="package-option__preview" for="package-choice-{{ $package->id }}">
                                                        <span class="package-option__title nvb-pkg__title">{{ $package->name }}</span>
                                                        @if (filled($package->description))
                                                            <span class="package-option__description nvb-pkg__desc">{{ $package->description }}</span>
                                                        @endif
                                                        <span class="package-option__facts nvb-pkg__facts">
                                                            <strong>{{ number_format((float) $package->price, 2) }} JOD</strong>
                                                            <span>{{ $package->min_guests }}–{{ $package->max_guests }} guests</span>
                                                        </span>
                                                    </label>
                                                    <label class="package-option__choice">
                                                        <input id="package-choice-{{ $package->id }}" type="radio" name="package_choice" value="{{ $package->id }}"
                                                               @checked((string) old('package_id') === (string) $package->id) disabled>
                                                        <span>Select</span>
                                                        <span class="package-option__sr-only nvp-sr">{{ $package->name }}</span>
                                                    </label>
                                                    <button type="button" class="package-option__details" data-package-open aria-haspopup="dialog">Details</button>

                                                    <dialog class="package-details-dialog" aria-labelledby="package-title-{{ $package->id }}">
                                                        <button type="button" class="space-details__more" data-package-close autofocus>Close</button>
                                                        <h3 id="package-title-{{ $package->id }}">{{ $package->name }}</h3>
                                                        @if (filled($package->description))
                                                            <p class="package-details-dialog__description">{{ $package->description }}</p>
                                                        @endif
                                                        <p>{{ number_format((float) $package->price, 2) }} JOD · {{ $package->min_guests }}–{{ $package->max_guests }} guests</p>
                                                        <h4>Included services</h4>
                                                        <ul>
                                                            @forelse ($package->items->whereNull('space_id') as $service)
                                                                <li>
                                                                    <strong>{{ $service->name }}</strong>
                                                                    @if (filled($service->description))
                                                                        <p class="package-details-dialog__description">{{ $service->description }}</p>
                                                                    @endif
                                                                </li>
                                                            @empty
                                                                <li>No additional services listed.</li>
                                                            @endforelse
                                                        </ul>
                                                    </dialog>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                    <nav class="package-pagination" aria-label="Package pages" data-package-pagination hidden></nav>
                                </fieldset>
                            @endif

                            @error('package_id')
                                <p class="nvb-alert" role="alert">{{ $message }}</p>
                            @enderror
                        </section>
                    </li>

                    {{-- 4. Date and time (optional here; confirmed on the next page) --}}
                    <li class="nvb-step" data-nvb-step="when">
                        <span class="nvb-step__num" aria-hidden="true">4</span>
                        <section class="nvb-step__body" aria-labelledby="nvb-when-heading">
                            <h3 id="nvb-when-heading">Date and time <small>can be set on the next step</small></h3>
                            <div class="nvb-when">
                                <label><span>Date</span><input id="nvb-date" name="date" type="date" min="{{ now()->toDateString() }}" value="{{ $prefill('date') }}"></label>
                                <label><span>From</span><input id="nvb-start" name="start_time" type="time" step="60" value="{{ $prefill('start_time') }}"></label>
                                <label><span>To</span><input id="nvb-end" name="end_time" type="time" step="60" value="{{ $prefill('end_time') }}"></label>
                            </div>
                            @foreach (['date', 'start_time', 'end_time'] as $field)
                                @error($field)
                                    <p class="nvb-alert" role="alert">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </section>
                    </li>
                </ol>

                {{-- 5. Summary and book --}}
                <div class="nvb-bar" aria-live="polite">
                    <span class="nvb-step__num" aria-hidden="true">5</span>
                    <div class="nvb-bar__summary">
                        <p class="nvb-bar__picks" data-nvb-picks>Choose an event type and a layout</p>
                        <p class="nvb-bar__total"><span data-nvb-total-label>Price</span> <b data-nvb-total>{{ $rate !== null ? number_format($rate, 2).' JOD / hour' : '—' }}</b></p>
                    </div>
                    <button class="space-booking-options__submit nvb-book" type="submit" form="space-booking-form" disabled>
                        Book
                    </button>
                </div>
            </form>
        </section>

        <div class="nvp-wrap nvp-space__grid nvp-space__grid--info">
            <section class="nvp-panel" aria-labelledby="overview-heading">
                <h2 id="overview-heading">Overview</h2>
                <p>{{ $space->description ?: 'No description available.' }}</p>
                <dl class="nvp-dl">
                    <div><dt>Capacity</dt><dd>{{ $space->capacity }} guests</dd></div>
                    @if ($rate !== null)
                        <div><dt>Hourly rate</dt><dd>{{ number_format($rate, 2) }} JOD</dd></div>
                    @endif
                    <div><dt>Venue</dt><dd>{{ $venue->name }}</dd></div>
                    @if (filled($venue->address))
                        <div><dt>City</dt><dd>{{ $venue->address }}</dd></div>
                    @endif
                </dl>
                @if ($photos->count() > 5)
                    <div class="nvp-thumbs">
                        @foreach ($photos->slice(5) as $image)
                            <img src="{{ $cl($image->image_url, 500) }}" alt="{{ $space->name }} — photo {{ $loop->iteration + 5 }}" loading="lazy">
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="nvp-panel space-overview" aria-labelledby="reviews-heading">
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

        <button type="button" class="nvb-open-bar" data-nvb-open>
            <span>@if ($rate !== null)<b>{{ number_format($rate, 2) }} JOD</b> / hour @else Book this space @endif</span>
            <span class="nvb-open-bar__cta">Book this space</span>
        </button>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/space-booking-options.js') }}" defer></script>
    <script src="{{ asset('js/convene-navy-booking.js') }}?v={{ @filemtime(public_path('js/convene-navy-booking.js')) }}" defer></script>
@endpush
