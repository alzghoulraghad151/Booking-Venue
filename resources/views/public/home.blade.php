<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Find a venue for your next gathering. Explore event spaces by city and guest count with Convene.">
    <title>Find your space | Convene</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-landing.css') }}">
</head>
<body class="convene-landing">
    <main>
        <section class="landing-hero" aria-labelledby="landing-title">
            <div class="landing-hero__content">
                <p class="landing-eyebrow">A space for every gathering</p>
                <h1 id="landing-title">Find the right space for your next event</h1>
                <p class="landing-hero__intro">From meaningful dinners to memorable celebrations, discover a venue that brings people together.</p>
                <a class="landing-button landing-button--warm" href="#explore-cities">Explore spaces <span aria-hidden="true">→</span></a>
            </div>
        </section>

        <form class="landing-search" action="{{ route('spaces.search') }}" method="GET" aria-label="Search event spaces">
            <div class="landing-search__field">
                <label for="landing-city">City</label>
                <input id="landing-city" name="city" type="text" placeholder="Where are you planning?" autocomplete="address-level2" required>
            </div>
            <div class="landing-search__field landing-search__field--guests">
                <label for="landing-capacity">Guests</label>
                <input id="landing-capacity" name="capacity" type="number" min="1" placeholder="Number of guests">
            </div>
            <button class="landing-button landing-button--navy" type="submit">Search spaces <span aria-hidden="true">→</span></button>
        </form>

        <section class="landing-section landing-cities" id="explore-cities" aria-labelledby="cities-title">
            <div class="landing-section__heading">
                <p class="landing-eyebrow landing-eyebrow--sage">Start with a place</p>
                <h2 id="cities-title">Explore spaces by city</h2>
                <p>Choose a city to find a space close to where your people are.</p>
            </div>

            <div class="landing-city-grid">
                @foreach (['Amman', 'Aqaba', 'Irbid', 'Zarqa'] as $index => $city)
                    <a class="landing-city-card landing-city-card--{{ $index + 1 }}" href="{{ route('spaces.search', ['city' => $city]) }}">
                        <span class="landing-city-card__number">0{{ $index + 1 }}</span>
                        <span class="landing-city-card__name">{{ $city }}</span>
                        <span class="landing-city-card__action">Explore spaces <span aria-hidden="true">↗</span></span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="landing-occasion" aria-labelledby="occasion-title">
            <div class="landing-occasion__intro">
                <p class="landing-eyebrow landing-eyebrow--warm">Make room for a good moment</p>
                <h2 id="occasion-title">Spaces for every kind of gathering</h2>
                <p>Find a setting that feels right for the people, plans, and moments that matter.</p>
            </div>
            <div class="landing-occasion__cards">
                <article class="landing-occasion-card landing-occasion-card--dinner">
                    <span class="landing-occasion-card__label">Gather around</span>
                    <h3>Dinner</h3>
                    <p>Thoughtful spaces for good food and better conversation.</p>
                </article>
                <article class="landing-occasion-card landing-occasion-card--celebration">
                    <span class="landing-occasion-card__label">Make a memory</span>
                    <h3>Celebrations</h3>
                    <p>Room for the moments you’ll want to remember.</p>
                </article>
                <article class="landing-occasion-card landing-occasion-card--meeting">
                    <span class="landing-occasion-card__label">Bring ideas together</span>
                    <h3>Meetings</h3>
                    <p>Comfortable places to think, plan, and connect.</p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>
