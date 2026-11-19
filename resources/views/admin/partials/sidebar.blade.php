<aside class="admin-sidebar">
    <div class="admin-brand">
        <a href="{{ route(Route::has('admin.dashboard') ? 'admin.dashboard' : 'admin.users.index') }}">
            <span class="admin-mark" aria-hidden="true">C</span>
            Convene
        </a>
    </div>

    <nav id="admin-navigation" class="admin-navigation" aria-label="Administration">
        <div class="admin-nav-group">
            <h2>Management</h2>
            <x-admin.nav-item label="Dashboard" route-name="admin.dashboard" active-pattern="admin.dashboard" />
            <x-admin.nav-item label="Venues" route-name="admin.venues.index" active-pattern="admin.venues.*" />
            <x-admin.nav-item label="Bookings" route-name="admin.bookings.index" active-pattern="admin.bookings.*" />
            @if (\Illuminate\Support\Facades\Route::has('admin.venues.index'))
                <x-admin.nav-item label="Venues" route-name="admin.venues.index" active-pattern="admin.venues.*" />
            @endif
            @if (\Illuminate\Support\Facades\Route::has('admin.bookings.index'))
                <x-admin.nav-item label="Bookings" route-name="admin.bookings.index" active-pattern="admin.bookings.*" />
            @endif
            <x-admin.nav-item label="Users" route-name="admin.users.index" active-pattern="admin.users.*" />
            @if (\Illuminate\Support\Facades\Route::has('admin.event-types.index'))
                <x-admin.nav-item label="Event Types" route-name="admin.event-types.index" active-pattern="admin.event-types.*" />
            @endif
        </div>
    </nav>
</aside>
