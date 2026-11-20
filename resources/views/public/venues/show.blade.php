<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $venue->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body>
    <main>
        <h1>{{ $venue->name }}</h1>
        <p>{{ $venue->address }}</p>

        @if ($venue->description)
            <p>{{ $venue->description }}</p>
        @endif

        @if ($venue->images->isNotEmpty())
            <section>
                <h2>Venue Gallery</h2>

                @foreach ($venue->images as $image)
                    <img src="{{ $image->image_url }}"
                         alt="{{ $venue->name }}"
                         width="280" loading="lazy">
                @endforeach
            </section>
        @endif

        <section>
            <h2>Spaces</h2>

            @forelse ($venue->spaces as $space)
                <article>
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
                        <p>{{ $space->hourly_rate }} JOD per hour</p>
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
                </article>
            @empty
                <p>No spaces are currently available at this venue.</p>
            @endforelse
        </section>
    </main>
</body>
</html>