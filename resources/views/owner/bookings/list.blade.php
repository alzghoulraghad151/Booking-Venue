@extends('layouts.owner')

@section('title', 'Bookings')
@section('subtitle', 'Review requests for your venues and make booking decisions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-bookings.css') }}">
@endpush

@section('content')
    <div class="bookings-page admin-bookings-page">

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
                    <p>Find bookings for your venues by organizer, status, space, or event date.</p>
                </div>
            </div>
            <form id="owner-booking-filters" class="filter-grid" action="{{ route('owner.bookings.index') }}" method="GET">
                <div class="field">
                    <label for="organizer">Organizer</label>
                    <input id="organizer" name="organizer" type="search" value="{{ $filters['organizer'] ?? '' }}" placeholder="Name or email">
                </div>
                <div class="field">
                    <label for="status">Booking status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="space_id">Space</label>
                    <select id="space_id" name="space_id">
                        <option value="">All spaces</option>
                        @foreach ($spaces as $space)
                            <option value="{{ $space->id }}" @selected((string) ($filters['space_id'] ?? '') === (string) $space->id)>
                                {{ $space->name }}@if ($space->venue) — {{ $space->venue->name }}@endif
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
                    <a class="admin-filter-reset" href="{{ route('owner.bookings.index') }}">Reset</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-heading panel-heading--row">
                <div>
                    <h2>Your venue bookings</h2>
                    <p>Booking status is shared by all details in this booking.</p>
                </div>
                <span class="result-count">{{ number_format($bookings->total()) }} results</span>
            </div>

            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Booking records">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Organizer</th>
                            <th>Venue</th>
                            <th>Event date</th>
                            <th>Spaces</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th scope="col" class="admin-table-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            @php
                                $firstDetail = $booking->details->sortBy('start_at')->first();
                                $eventStart = $firstDetail?->start_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE);
                            @endphp
                            <tr>
                                <td><a class="booking-id" href="{{ route('owner.bookings.show', $booking) }}">#{{ $booking->id }}</a></td>
                                <td>
                                    <span class="cell-primary">{{ $booking->customer?->name ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $booking->customer?->email ?? 'No email' }}</span>
                                </td>
                                <td>{{ $booking->venue?->name ?? '—' }}</td>
                                <td class="nowrap">{{ $eventStart?->format('d M Y') ?? '—' }}</td>
                                <td>{{ number_format($booking->details_count) }}</td>
                                <td class="amount">{{ number_format((float) $booking->details_sum_total_price, 2) }} JOD</td>
                                <td><span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                                <td class="admin-table-actions">
                                    <a class="admin-table-icon" href="{{ route('owner.bookings.show', $booking) }}" aria-label="View details for booking {{ $booking->id }}" title="View">
                                        <x-admin.table-action-icon name="view" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <span class="empty-mark" aria-hidden="true">E</span>
                                        <strong>No bookings found</strong>
                                        <span>No bookings were found for your venues. Try changing your filters.</span>
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