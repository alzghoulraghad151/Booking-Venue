@extends('layouts.owner')

@section('title', 'Owner Dashboard')
@section('subtitle', 'Track your venues, spaces, and booking activity.')

@section('content')
    <div class="dashboard-stats admin-dashboard-stats" aria-label="Venue and booking statistics">
        <article><h2>Total Venues</h2><strong>{{ $totalVenues }}</strong></article>
        <article><h2>Total Spaces</h2><strong>{{ $totalSpaces }}</strong></article>
        <article><h2>Pending Booking Details</h2><strong>{{ $pendingDetails }}</strong></article>
        <article><h2>Confirmed Booking Details</h2><strong>{{ $confirmedDetails }}</strong></article>
    </div>

    <section class="dashboard-panel dashboard-quick-actions" aria-labelledby="owner-actions-title">
        <h2 id="owner-actions-title">Quick Actions</h2>
        <ul>
            @foreach (['Add Venue' => 'owner.venues.create', 'Add Space' => 'owner.spaces.create', 'Add Package' => 'owner.packages.create'] as $label => $routeName)
                <li>
                    @if (Route::has($routeName) && Route::getRoutes()->getByName($routeName)->parameterNames() === [])
                        <a href="{{ route($routeName) }}"><strong>{{ $label }}</strong></a>
                    @else
                        <span>{{ $label }}</span><span class="dashboard-note">Coming soon</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <div class="dar-owner-dashboard-grid">
        <section class="dashboard-panel" aria-labelledby="owner-bookings-title">
            <header class="dashboard-panel-header">
                <h2 id="owner-bookings-title">Recent Bookings</h2>
                @if (Route::has('owner.bookings.index'))
                    <a href="{{ route('owner.bookings.index') }}">View all bookings</a>
                @endif
            </header>

            <div class="dashboard-table-wrap" tabindex="0" role="region" aria-labelledby="owner-bookings-title">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th scope="col">Booking #</th>
                            <th scope="col">Organizer</th>
                            <th scope="col">Venue</th>
                            <th scope="col">Requested On</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentBookings as $booking)
                            <tr>
                                <td>
                                    @if (Route::has('owner.bookings.show'))
                                        <a href="{{ route('owner.bookings.show', $booking) }}">#{{ $booking->id }}</a>
                                    @else
                                        #{{ $booking->id }}
                                    @endif
                                </td>
                                <td>{{ $booking->customer?->name ?? '—' }}</td>
                                <td>{{ $booking->venue?->name ?? '—' }}</td>
                                <td>{{ $booking->created_at?->format('d M Y') ?? '—' }}</td>
                                <td><span class="dashboard-badge">{{ ucfirst($booking->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-panel" aria-labelledby="owner-spaces-title">
            <header class="dashboard-panel-header">
                <h2 id="owner-spaces-title">My Spaces</h2>
                @if (Route::has('owner.spaces.index'))
                    <a href="{{ route('owner.spaces.index') }}">View all spaces</a>
                @endif
            </header>

            @if ($recentSpaces->isEmpty())
                <p>No spaces added yet.</p>
            @else
                <ul class="dashboard-review-list">
                    @foreach ($recentSpaces as $space)
                        <li>
                            <div>
                                <strong>{{ $space->name }}</strong>
                                <span class="dashboard-note">{{ $space->venue?->name ?? '—' }}</span>
                            </div>
                            <span class="dashboard-badge">{{ ucfirst($space->status) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
