<aside class="admin-sidebar admin-sidebar--management admin-sidebar--owner">
    <div class="admin-brand">
        <a href="{{ route('owner.dashboard') }}">
            <span class="dar-sidebar-wordmark"><i class="nv-mark" aria-hidden="true"></i><span class="nv-word">CON<b>VENE</b></span></span>
            <span class="dar-sidebar-role">Venue Partner</span>
        </a>
    </div>

    <nav class="admin-navigation" aria-label="Owner workspace">
        <div class="admin-nav-group">
            <h2>Management</h2>
            <x-admin.nav-item label="Dashboard" route-name="owner.dashboard" active-pattern="owner.dashboard" />
            <x-admin.nav-item label="My Venues" route-name="owner.venues.index" active-pattern="owner.venues.*" />
            <x-admin.nav-item label="My Spaces" route-name="owner.spaces.index" active-pattern="owner.spaces.*" />
            <x-admin.nav-item label="Space Layouts" route-name="owner.space-layouts.index" active-pattern="owner.space-layouts.*" />
            <x-admin.nav-item label="Packages" route-name="owner.packages.index" active-pattern="owner.packages.*" />
            <x-admin.nav-item label="Bookings" route-name="owner.bookings.index" active-pattern="owner.bookings.*" />
            <x-admin.nav-item label="Calendar" route-name="owner.calendar.index" active-pattern="owner.calendar.*" />
        </div>

        <div class="admin-nav-group">
            <h2>Account</h2>
            <x-admin.nav-item label="Profile" route-name="owner.profile.edit" active-pattern="owner.profile.*" />
        </div>
    </nav>

    <form class="admin-sidebar-logout" method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="admin-nav-item" type="submit">
            <x-admin.management-nav-icon name="logout" />
            Logout
        </button>
    </form>
</aside>