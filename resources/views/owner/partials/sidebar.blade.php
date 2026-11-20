<aside class="admin-sidebar">
    <div class="admin-brand">
        <a href="{{ route('owner.venues.index') }}">
            <span class="admin-mark" aria-hidden="true">C</span>
            Convene
        </a>
    </div>

    <nav class="admin-navigation" aria-label="Venue owner">
        <div class="admin-nav-group">
            <x-admin.nav-item label="My Venues" route-name="owner.venues.index" active-pattern="owner.venues.*" />
            @if (Route::has('owner.spaces.index'))
                <x-admin.nav-item label="My Spaces" route-name="owner.spaces.index" active-pattern="owner.spaces.*" />
            @endif
            @if (Route::has('owner.space-layouts.index'))
                <x-admin.nav-item label="Space Layouts" route-name="owner.space-layouts.index" active-pattern="owner.space-layouts.*" />
            @endif
        </div>
    </nav>
</aside>
