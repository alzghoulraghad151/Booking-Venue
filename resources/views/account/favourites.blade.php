@extends('layouts.users')

@section('title', 'Favourites')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/favourites.css') }}">
@endpush

@section('content')
    <div class="favourites-page">
        <div class="favourites-page__content">
            <h1>Favourites</h1>

            @if (session('favourite_status'))
                <p role="status">{{ session('favourite_status') }}</p>
            @endif

            @if ($spaces->isNotEmpty())
                <div class="favourites-heading">
                    <h2>Event Spaces</h2>
                    <p>{{ $spaces->count() }} {{ $spaces->count() === 1 ? 'space' : 'spaces' }}</p>
                </div>

                @foreach ($spaces as $space)
                    <article class="favourite-card">
                        @if ($image = $space->images->first())
                            <img class="favourite-card__image"
                                 src="{{ $image->image_url }}"
                                 alt="{{ $space->name }}"
                                 loading="lazy">
                        @endif

                        <div class="favourite-card__details">
                            <form class="favourite-card__remove" method="POST"
                                  action="{{ route('account.favourites.destroy', ['space' => $space->id]) }}">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        aria-label="Remove {{ $space->name }} from favourites">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </form>

                            <div class="favourite-card__summary">
                                <div>
                                    <h3>{{ $space->name }}</h3>
                                    <p>{{ $space->venue->name }}</p>
                                    <p class="favourite-card__address">{{ $space->venue->address }}</p>
                                </div>

                                @if ($space->hourly_rate !== null)
                                    <p class="favourite-card__price">
                                        <strong>{{ number_format((float) $space->hourly_rate, 2) }} JOD</strong>
                                        <span>per hour</span>
                                    </p>
                                @endif
                            </div>

                            <p class="favourite-card__capacity">
                                Up to {{ $space->capacity }} {{ $space->capacity == 1 ? 'guest' : 'guests' }}
                            </p>
                        </div>
                    </article>
                @endforeach

                <div class="favourites-footer">
                    <a class="favourites-link" href="{{ route('spaces.search') }}">
                        Search for more spaces
                    </a>
                </div>
            @else
                <div class="favourites-empty">
                    <span class="favourites-empty__heart" aria-hidden="true">♥</span>
                    <p>You don’t have any favourite spaces yet.</p>

                    <a class="favourites-link favourites-link--primary" href="{{ route('spaces.search') }}">
                        Explore spaces
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection