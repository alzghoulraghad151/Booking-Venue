<footer class="site-footer">
    <div class="site-container">
        <div class="site-footer-grid">
            <div class="site-footer-brand">
                @include('partials.site.brand')
                <p>Find spaces in Jordan for meetings, conferences, and company events.</p>
            </div>

            <nav class="site-footer-group" aria-labelledby="site-footer-explore">
                <h2 id="site-footer-explore">Explore</h2>
                <ul>
                    <li><a href="{{ route('spaces.search') }}">Browse spaces</a></li>
                    @if (Route::has('home'))
                        <li><a href="{{ route('home') }}">Home</a></li>
                    @endif
                </ul>
            </nav>

            @guest
                @if (Route::has('login') || Route::has('register') || Route::has('password.request'))
                    <nav class="site-footer-group" aria-labelledby="site-footer-account">
                        <h2 id="site-footer-account">Account</h2>
                        <ul>
                            @foreach (['login' => 'Sign in', 'register' => 'Create account', 'password.request' => 'Reset password'] as $name => $label)
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
