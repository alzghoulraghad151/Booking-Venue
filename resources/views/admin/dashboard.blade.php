@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="dashboard-stats admin-dashboard-stats" aria-label="Platform statistics">
        <article>
            <h2>Total Users</h2>
            <strong>{{ number_format($totalUsers) }}</strong>
            <p>{{ number_format($totalOwners) }} venue owners</p>
        </article>
        <article>
            <h2>Total Venues</h2>
            <strong>{{ number_format($totalVenues) }}</strong>
            <p>Across all venue statuses</p>
        </article>
        <article>
            <h2>Total Bookings</h2>
            <strong>{{ number_format($totalBookings) }}</strong>
            <p>Across all booking statuses</p>
        </article>
        <article>
            <h2>Pending Bookings</h2>
            <strong>{{ number_format($pendingBookings) }}</strong>
            <p>Requests awaiting a decision</p>
        </article>
    </div>

    @include('admin.partials.quick-actions')

    <section class="dashboard-panel" aria-labelledby="recent-bookings-title">
        <header class="dashboard-panel-header">
            <h2 id="recent-bookings-title">Recent Bookings</h2>
            @if (Route::has('admin.bookings.index'))
                <a class="dashboard-panel-action" href="{{ route('admin.bookings.index') }}">View all bookings</a>
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
                <h2 id="venues-review-title">Venues Awaiting Review <span class="dashboard-badge">{{ $pendingVenues }}</span></h2>
                @if (Route::has('admin.venues.index'))
                    <a class="dashboard-panel-action" href="{{ route('admin.venues.index', ['status' => 'pending']) }}">Manage venues</a>
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
            <p class="dashboard-note">{{ $monthLabel }} · Asia/Amman</p>
            <dl class="dashboard-metrics">
                <div><dt>Paid This Month</dt><dd>{{ number_format($monthlyPaid, 2) }} JOD</dd></div>
                <div><dt>Commission This Month</dt><dd>{{ number_format($monthlyCommission, 2) }} JOD</dd></div>
            </dl>
            <p class="dashboard-note">Paid totals use the payment date. Commission uses the start date of each confirmed or completed booking detail.</p>

            <p class="dashboard-note">Payment counts · all time</p>
            <dl class="dashboard-metrics">
                <div><dt>Pending</dt><dd>{{ $paymentCounts['pending'] ?? 0 }}</dd></div>
                <div><dt>Paid</dt><dd>{{ $paymentCounts['paid'] ?? 0 }}</dd></div>
                <div><dt>Failed</dt><dd>{{ $paymentCounts['failed'] ?? 0 }}</dd></div>
                <div><dt>Refunded</dt><dd>{{ $paymentCounts['refunded'] ?? 0 }}</dd></div>
            </dl>
        </section>
    </div>
@endsection
