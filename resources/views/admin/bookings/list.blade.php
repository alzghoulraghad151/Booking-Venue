@extends('layouts.admin')

@section('title', 'Bookings Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-bookings.css') }}">
@endpush

@section('content')
    <div class="bookings-page admin-bookings-page">
        <p class="page-subtitle">Review booking activity across all Convene venues.</p>
        @if (session('success'))
            <div class="alert alert--success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Search and filters</h2>
                    <p>Find bookings by organizer, status, venue, or event date.</p>
                </div>
            </div>

            <form id="admin-booking-filters" class="filter-grid" action="{{ route('admin.bookings.index') }}" method="GET">
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
                <div class="field">
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        @foreach ([
                            'newest' => 'Newest first',
                            'oldest' => 'Oldest first',
                            'event_date_asc' => 'Event date (earliest)',
                            'event_date_desc' => 'Event date (latest)',
                            'total_desc' => 'Total (highest)',
                            'total_asc' => 'Total (lowest)',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="button admin-filter-submit" type="submit">Apply filters</button>
                    <a class="admin-filter-reset" href="{{ route('admin.bookings.index') }}">Reset</a>
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

            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Bookings table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Organizer</th>
                            <th>Venue</th>
                            <th>Event type</th>
                            <th>Event date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th scope="col" class="admin-table-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            @php
                                $firstDetail = $booking->details->sortBy('start_at')->first();
                                $total = $booking->details_sum_total_price ?? $booking->details->sum('total_price');
                            @endphp
                            <tr>
                                <td><a class="booking-id" href="{{ route('admin.bookings.show', $booking) }}">#{{ $booking->id }}</a></td>
                                <td>
                                    <span class="cell-primary">{{ $booking->customer?->name ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $booking->customer?->email ?? 'No email' }}</span>
                                </td>
                                <td>{{ $booking->venue?->name ?? '—' }}</td>
                                <td>{{ $firstDetail?->eventType?->name ?? '—' }}</td>
                                <td class="nowrap">{{ $firstDetail?->start_at?->format('d M Y') ?? '—' }}</td>
                                <td class="amount">{{ number_format((float) $total, 2) }} JOD</td>
                                <td><span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                                <td class="admin-table-actions">
                                    <a class="admin-table-icon" href="{{ route('admin.bookings.show', $booking) }}" aria-label="View details for booking {{ $booking->id }}" title="View">
                                        <x-admin.table-action-icon name="view" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <strong>No bookings found</strong>
                                        <span>Try changing or resetting your filters.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $bookings->links('components.admin.pagination') }}
        </section>
    </div>
@endsection