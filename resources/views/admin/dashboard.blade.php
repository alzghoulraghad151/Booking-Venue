@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="dashboard-stats" aria-label="Platform statistics">
        <article><h2>Total Users</h2><p>{{ $totalUsers }}</p></article>
        <article><h2>Total Venues</h2><p>{{ $totalVenues }}</p></article>
        <article><h2>Total Bookings</h2><p>{{ $totalBookings }}</p></article>
        <article><h2>Venue Owners</h2><p>{{ $totalOwners }}</p></article>
        <article><h2>Pending Venues</h2><p>{{ $pendingVenues }}</p></article>
        <article><h2>Pending Bookings</h2><p>{{ $pendingBookings }}</p></article>
    </div>

    <section class="dashboard-panel" aria-labelledby="recent-bookings-title">
        <header class="dashboard-panel-header">
            <h2 id="recent-bookings-title">Recent Bookings</h2>
            @if (Route::has('admin.bookings.index'))
                <a href="{{ route('admin.bookings.index') }}">View all bookings</a>
            @endif
        </header>
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-labelledby="recent-bookings-title">
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
                                @if (Route::has('admin.bookings.show'))
                                    <a href="{{ route('admin.bookings.show', $booking) }}">#{{ $booking->id }}</a>
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

    <div class="dashboard-summary-grid">
        <section class="dashboard-panel" aria-labelledby="venues-review-title">
            <header class="dashboard-panel-header">
                <h2 id="venues-review-title">Venues Awaiting Review</h2>
                @if (Route::has('admin.venues.index'))
                    <a href="{{ route('admin.venues.index', ['status' => 'pending']) }}">Manage venues</a>
                @endif
            </header>

            @if ($venuesAwaitingReview->isEmpty())
                <p>No venues awaiting review.</p>
            @else
                <ul class="dashboard-review-list">
                    @foreach ($venuesAwaitingReview as $venue)
                        <li>
                            @if (Route::has('admin.venues.show'))
                                <a href="{{ route('admin.venues.show', $venue) }}">{{ $venue->name }}</a>
                            @else
                                {{ $venue->name }}
                            @endif
                            <span class="dashboard-badge">Pending</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="dashboard-panel" aria-labelledby="payments-title">
            <h2 id="payments-title">Payments &amp; Commission</h2>
            <dl class="dashboard-metrics">
                <div><dt>Total Paid</dt><dd>{{ number_format($totalPaid, 2) }} JOD</dd></div>
                <div><dt>Total Commission</dt><dd>{{ number_format($totalCommission, 2) }} JOD</dd></div>
            </dl>
            <p class="dashboard-note">Commission on confirmed and completed bookings.</p>

            <dl class="dashboard-metrics">
                <div><dt>Pending</dt><dd>{{ $paymentCounts['pending'] ?? 0 }}</dd></div>
                <div><dt>Paid</dt><dd>{{ $paymentCounts['paid'] ?? 0 }}</dd></div>
                <div><dt>Failed</dt><dd>{{ $paymentCounts['failed'] ?? 0 }}</dd></div>
                <div><dt>Refunded</dt><dd>{{ $paymentCounts['refunded'] ?? 0 }}</dd></div>
            </dl>
        </section>
    </div>

    <section id="reviews" class="dashboard-panel" aria-labelledby="reviews-title">
        <header class="dashboard-panel-header">
            <h2 id="reviews-title">Reviews</h2>
            <p class="dashboard-badge">Average rating: {{ $averageRating === null ? 'No ratings yet' : number_format($averageRating, 1) . ' / 5' }}</p>
        </header>
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-labelledby="reviews-title">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th scope="col">Booking #</th>
                        <th scope="col">Organizer</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Comment</th>
                        <th scope="col">Reviewed On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>#{{ $review->booking_id }}</td>
                            <td>{{ $review->booking?->customer?->name ?? '—' }}</td>
                            <td>{{ $review->booking?->venue?->name ?? '—' }}</td>
                            <td>{{ $review->rating }} / 5</td>
                            <td>{{ $review->comment ?? '—' }}</td>
                            <td>{{ $review->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No reviews on this page.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <nav class="dashboard-panel-header dashboard-pagination" aria-label="Review pages">
                @if ($reviews->previousPageUrl())
                    <a href="{{ $reviews->previousPageUrl() }}#reviews">Previous</a>
                @endif
                <span>Page {{ $reviews->currentPage() }}</span>
                @if ($reviews->nextPageUrl())
                    <a href="{{ $reviews->nextPageUrl() }}#reviews">Next</a>
                @endif
            </nav>
        @endif
    </section>
@endsection
