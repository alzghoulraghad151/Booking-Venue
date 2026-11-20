<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $venue->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/venue-details.css') }}">
</head>
<body class="venue-details">
    <main class="venue-details__content">
        <header class="venue-details__header">
            <h1>{{ $venue->name }}</h1>
            <p>{{ $venue->address }}</p>

            @if ($venue->description)
                <p>{{ $venue->description }}</p>
            @endif
        </header>

        @if ($venue->images->isNotEmpty())
            <section>
                <h2>Venue Gallery</h2>

                <div class="venue-details__gallery">
                    @foreach ($venue->images as $image)
                        <img src="{{ $image->image_url }}"
                             alt="{{ $venue->name }}"
                             width="280" loading="lazy">
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <h2>Spaces</h2>

            <div class="venue-details__cards">
                @forelse ($venue->spaces as $space)
                    <article class="venue-details__space">
                        <h3>{{ $space->name }}</h3>

                        @foreach ($space->images as $image)
                            @if (\Illuminate\Support\Str::startsWith($image->image_url, 'https://'))
                                <img src="{{ $image->image_url }}"
                                     alt="{{ $space->name }}"
                                     width="220" loading="lazy">
                            @endif
                        @endforeach

                        @if ($space->description)
                            <p>{{ $space->description }}</p>
                        @endif

                        <p>
                            Capacity: {{ $space->capacity }}
                            {{ $space->capacity == 1 ? 'guest' : 'guests' }}
                        </p>

                        @if ($space->hourly_rate !== null)
                            <p><strong>{{ $space->hourly_rate }} JOD per hour</strong></p>
                        @endif

                        <h4>Space Layouts</h4>

                        <ul>
                            @forelse ($space->spaceLayouts as $layout)
                                <li>
                                    {{ $layout->name }} — {{ $layout->capacity }}
                                    {{ $layout->capacity == 1 ? 'guest' : 'guests' }}

                                    @if ($layout->eventTypes->isNotEmpty())
                                        <br>
                                        Event types: {{ $layout->eventTypes->pluck('name')->join(', ') }}
                                    @endif
                                </li>
                            @empty
                                <li>No layouts available for this space.</li>
                            @endforelse
                        </ul>

                        <h4>Packages</h4>

                        @forelse ($space->packages as $package)
                            <div class="venue-details__package">
                                <h5>{{ $package->name }}</h5>

                                @if ($package->description)
                                    <p>{{ $package->description }}</p>
                                @endif

                                <p><strong>{{ $package->price }} JOD</strong></p>

                                @if ($package->items->isNotEmpty())
                                    <p>Included services</p>
                                    <ul>
                                        @foreach ($package->items as $item)
                                            <li>{{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @empty
                            <p>No packages available for this space.</p>
                        @endforelse
                    </article>
                @empty
                    <p>No spaces are currently available at this venue.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2>Reviews</h2>

            @if ($venue->reviews->isNotEmpty())
                <p>
                    Average rating:
                    <strong>{{ number_format($venue->reviews->avg('rating'), 1) }} / 5</strong>
                </p>
            @endif

            <div class="venue-details__cards">
                @forelse ($venue->reviews as $review)
                    <article class="venue-details__review">
                        <p><strong>Rating: {{ $review->rating }} / 5</strong></p>

                        @if ($review->comment)
                            <p>{{ $review->comment }}</p>
                        @endif

                        @if ($review->created_at)
                            <time datetime="{{ $review->created_at->toDateString() }}">
                                {{ $review->created_at->format('d M Y') }}
                            </time>
                        @endif
                    </article>
                @empty
                    <p>No reviews yet.</p>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>