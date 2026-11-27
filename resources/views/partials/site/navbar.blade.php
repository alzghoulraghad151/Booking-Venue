<header class="site-header site-header--dar" data-site-header>
    <div class="site-container site-header-inner">
        @include('partials.site.brand')

        <button class="site-menu-toggle" type="button" aria-controls="site-navigation" aria-expanded="false" data-site-menu-toggle hidden>
            <span class="site-menu-icon" aria-hidden="true"></span>
            Menu
        </button>

        <nav class="site-navigation" id="site-navigation" aria-label="Main navigation">
            <div class="site-nav-links">
                @if (Route::has('home'))
                    <a class="site-link" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Home</a>
                @endif
                <a class="site-link" href="{{ route('spaces.search') }}" @if (request()->routeIs('spaces.search', 'spaces.show')) aria-current="page" @endif>Explore Spaces</a>
                @if (Route::has('bookings.from-invite'))
                    <a class="site-link" href="{{ route('bookings.from-invite') }}"   @if (request()->routeIs('bookings.from-invite*')) aria-current="page" @endif>AI Booking Assistant</a>
                @endif
            </div>

            <div class="site-nav-account">
                @stack('nav-tools')
                @guest
                    @if (Route::has('login'))
                        <a class="site-link" href="{{ route('login') }}">Login</a>
                    @endif
                    @if (Route::has('register'))
                        <a class="site-button" href="{{ route('register') }}">Sign Up</a>
                    @endif
                @else
                    @if (auth()->user()->role === 'organizer')
                        @foreach (['my-bookings.index' => 'My bookings', 'account.favourites.index' => 'Favorites'] as $name => $label)
                            @if (Route::has($name))
                                <a class="site-link site-account-link" href="{{ route($name) }}" @if (request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
                            @endif
                        @endforeach
                    @endif
                    @if (auth()->user()->role === 'venue_owner' && Route::has('owner.venues.index'))
                        <a class="site-button" href="{{ route('owner.venues.index') }}">Manage venues</a>
                    @elseif (auth()->user()->role === 'admin' && Route::has('admin.dashboard'))
                        <a class="site-button" href="{{ route('admin.dashboard') }}">Admin dashboard</a>
                    @elseif (Route::has('account.personal-details.edit'))
                        <a class="site-link" href="{{ route('account.personal-details.edit') }}">My profile</a>
                    @endif

                    @php($logoutRoute = Route::has('logout') ? Route::getRoutes()->getByName('logout') : Route::getRoutes()->getByName('admin.logout'))
                    @if ($logoutRoute && in_array('POST', $logoutRoute->methods(), true))
                        <form method="POST" action="{{ route($logoutRoute->getName()) }}">
                            @csrf
                            <button class="site-link" type="submit">Sign out</button>
                        </form>
                    @endif
                @endguest
            </div>
        </nav>
    </div>
</header>
