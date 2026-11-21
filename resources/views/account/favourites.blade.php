@extends('layouts.users')

@section('title', 'Favourites')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/favourites.css') }}">
@endpush

@section('content')
    <div class="favourites-page">
        <div class="favourites-page__content">
            <header class="favourites-page__heading">
                <h1>Favourites</h1>
            </header>

            @if (session('favourite_status'))
                <p role="status">{{ session('favourite_status') }}</p>
            @endif

            @if ($spaces->isNotEmpty())
                <div class="favourites-heading">
                    <h2>Event Spaces</h2>
                    <p>{{ $spaces->count() }} {{ $spaces->count() === 1 ? 'space' : 'spaces' }}</p>
                </div>

                <div class="favourites-list">
                    @foreach ($spaces as $space)
                        <article class="favourite-card">
                            @if ($image = $space->images->first())
                                <img class="favourite-card__image"
                                     src="{{ $image->image_url }}"
                                     alt="{{ $space->name }}"
                                     loading="lazy">
                            @endif

                            <div class="favourite-card__details">
                                <div class="favourite-card__heading">
                                    <h3>{{ $space->name }}</h3>

                                    <form method="POST"
                                          action="{{ route('account.favourites.destroy', ['space' => $space->id]) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button class="favourite-card__remove" type="submit"
                                                aria-label="Remove {{ $space->name }} from favourites">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </form>
                                </div>

                                <p class="favourite-card__venue">{{ $space->venue->name }}</p>
                                <p class="favourite-card__address">{{ $space->venue->address }}</p>

                                <div class="favourite-card__meta">
                                    <p>
                                        Up to {{ $space->capacity }}
                                        {{ $space->capacity == 1 ? 'guest' : 'guests' }}
                                    </p>

                                    @if ($space->hourly_rate !== null)
                                        <p>
                                            <strong>{{ number_format((float) $space->hourly_rate, 2) }} JOD</strong>
                                            <span>per hour</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="favourites-footer">
                    <a class="favourites-link" href="{{ route('spaces.search') }}">
                        Search for more spaces
                    </a>
                </div>
            @else
                <div class="favourites-empty">
                    <svg class="favourites-empty__heart" width="32" height="32"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true" focusable="false">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8l8.8 8.8 8.8-8.8a5.5 5.5 0 0 0 0-7.8z"></path>
                    </svg>

                    <h2>You don’t have any favourite spaces yet.</h2>

                    <a class="favourites-link favourites-link--primary" href="{{ route('spaces.search') }}">
                        Explore spaces
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection