<footer class="site-footer site-footer--dar">
    <div class="site-container">
        <div class="site-footer-grid">
            <div class="site-footer-brand" id="about">
                @include('partials.site.brand')
                <p>Discover and book individual event spaces for business gatherings across Jordan.</p>
            </div>

            <nav class="site-footer-group" aria-labelledby="site-footer-explore">
                <h2 id="site-footer-explore">Platform</h2>
                <ul>
                    <li><a href="{{ route('spaces.search') }}">Explore Spaces</a></li>
                    @if (Route::has('home'))
                        <li><a href="{{ route('home') }}#event-types">Event Categories</a></li>
                        <li><a href="{{ route('home') }}#about">About Convene</a></li>
                    @endif
                </ul>
            </nav>

            @guest
                @if (Route::has('login') || Route::has('register') || Route::has('password.request'))
                    <nav class="site-footer-group" aria-labelledby="site-footer-account">
                        <h2 id="site-footer-account">Account</h2>
                        <ul>
                            @foreach (['login' => 'Organizer Login', 'register' => 'Sign Up', 'password.request' => 'Reset password'] as $name => $label)
                                @if (Route::has($name))
                                    <li><a href="{{ route($name) }}">{{ $label }}</a></li>
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                @endif
            @endguest
        </div>

        <p class="site-footer-legal">© {{ now()->year }} Convene</p>
    </div>
</footer>
