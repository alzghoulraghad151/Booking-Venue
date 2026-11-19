@extends('layouts.bookings')

@section('title', 'Bookings Management')
@section('heading', 'Bookings Management')
@section('subtitle', 'Review booking activity across all EventPlan venues.')

@section('content')
    <section class="metric-grid" aria-label="Booking summary">
        <article class="metric-card">
            <span class="metric-label">Total bookings</span>
            <strong class="metric-value">{{ number_format($summary['total']) }}</strong>
            <span class="metric-note">All recorded requests</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Pending</span>
            <strong class="metric-value">{{ number_format($summary['pending']) }}</strong>
            <span class="metric-note">Waiting for an owner decision</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Confirmed</span>
            <strong class="metric-value">{{ number_format($summary['confirmed']) }}</strong>
            <span class="metric-note">Approved by venue owners</span>
        </article>
        <article class="metric-card metric-card--warm">
            <span class="metric-label">Completed</span>
            <strong class="metric-value">{{ number_format($summary['completed']) }}</strong>
            <span class="metric-note">Completed bookings</span>
        </article>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Search and filters</h2>
                <p>Find bookings by organizer, status, venue, or event date.</p>
            </div>
        </div>

        <form class="filter-grid" action="{{ route('admin.bookings.index') }}" method="GET">
            <div class="field">
                <label for="organizer">Organizer</label>
                <input id="organizer" name="organizer" type="search" value="{{ $filters['organizer'] ?? '' }}" placeholder="Name or email">
            </div>
            <div class="field">
                <label for="status">Booking status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="venue_id">Venue</label>
                <select id="venue_id" name="venue_id">
                    <option value="">All venues</option>
                    @foreach ($venues as $venue)
                        <option value="{{ $venue->id }}" @selected((string) ($filters['venue_id'] ?? '') === (string) $venue->id)>
                            {{ $venue->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="date">Event date</label>
                <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}">
            </div>
            <div class="filter-actions">
                <button class="button button--primary" type="submit">Apply filters</button>
                <a class="button button--quiet" href="{{ route('admin.bookings.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading panel-heading--row">
            <div>
                <h2>All bookings</h2>
                <p>Admin access is read-only. Booking decisions are made by venue owners.</p>
            </div>
            <span class="result-count">{{ number_format($bookings->total()) }} results</span>
        </div>

        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Organizer</th>
                        <th>Venue</th>
                        <th>Event type</th>
                        <th>Event date</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        @php
                            $firstDetail = $booking->details->sortBy('start_at')->first();
                            $total = $booking->details->sum('total_price');
                            $guests = $booking->details->sum('guest_count');
                        @endphp
                        <tr>
                            <td><a class="booking-id" href="{{ route('admin.bookings.show', $booking) }}">#{{ $booking->id }}</a></td>
                            <td>
                                <span class="cell-primary">{{ $booking->customer?->name ?? '—' }}</span>
                                <span class="cell-secondary">{{ $booking->customer?->email ?? 'No email' }}</span>
                            </td>
                            <td>{{ $booking->venue?->name ?? '—' }}</td>
                            <td>{{ $firstDetail?->eventType?->name ?? '—' }}</td>
                            <td>{{ $firstDetail?->start_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ number_format($guests) }}</td>
                            <td class="amount">{{ number_format((float) $total, 2) }}</td>
                            <td><span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            <td><a class="table-link" href="{{ route('admin.bookings.show', $booking) }}">View</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <span class="empty-mark" aria-hidden="true">E</span>
                                    <strong>No bookings found</strong>
                                    <span>Add booking records to the database or change your filters.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $bookings->links('components.pagination') }}
    </section>
@endsection
