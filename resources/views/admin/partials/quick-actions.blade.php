<section class="dashboard-panel dashboard-quick-actions" aria-labelledby="quick-actions-title">
    <h2 id="quick-actions-title">Quick Actions</h2>
    <ul>
        @if (Route::has('admin.bookings.index'))
            <li>
                <a href="{{ route('admin.bookings.index') }}">
                    <strong>Manage Bookings</strong>
                    <span>Review requests and booking statuses</span>
                </a>
            </li>
        @endif
        @if (Route::has('admin.venues.index'))
            <li>
                <a href="{{ route('admin.venues.index', ['status' => 'pending']) }}">
                    <strong>Review Venues</strong>
                    <span>Open venues awaiting approval</span>
                </a>
            </li>
        @endif
        @if (Route::has('admin.users.index'))
            <li>
                <a href="{{ route('admin.users.index') }}">
                    <strong>Manage Users</strong>
                    <span>Find and review platform accounts</span>
                </a>
            </li>
        @endif
    </ul>
</section>
