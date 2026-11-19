
@extends('layouts.admin')

@section('title', 'Bookings Management')

@section('content')

<div class="booking-page">

    <p class="booking-description">
        View and monitor all customer bookings.
    </p>

    <div class="booking-card">

        <h2 class="booking-section-title">Search & Filters</h2>

        <form action="{{ route('admin.bookings.index') }}" method="GET">

            <div class="booking-filters">

                <div class="booking-field">
                    <label for="organizer">Organizer</label>
                    <input
                        type="text"
                        id="organizer"
                        name="organizer"
                        value="{{ request('organizer') }}"
                        placeholder="Name or email">
                </div>

                <div class="booking-field">
                    <label for="status">Booking Status</label>

                    <select id="status" name="status">
                        <option value="">All Statuses</option>

                        @foreach(['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $status)
                            <option
                                value="{{ $status }}"
                                @selected(request('status') == $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="booking-field">
                    <label for="venue_id">Venue</label>

                    <select id="venue_id" name="venue_id">
                        <option value="">All Venues</option>

                        @foreach($venues as $venue)
                            <option
                                value="{{ $venue->id }}"
                                @selected(request('venue_id') == $venue->id)>
                                {{ $venue->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="booking-field">
                    <label for="date">Booking Date</label>

                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ request('date') }}">
                </div>

                <div>
                    <button type="submit" class="booking-btn">
                        Search
                    </button>

                    <a
                        href="{{ route('admin.bookings.index') }}"
                        class="booking-btn booking-btn-light">
                        Reset
                    </a>
                </div>

            </div>

        </form>

    </div>

    <div class="booking-card">

        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">

            <div>
                <h2 class="booking-section-title" style="margin-bottom:4px;">
                    All Bookings
                </h2>

                <p class="booking-description">
                    View booking information and details.
                </p>
            </div>

            <span style="color:var(--convene-sage); font-weight:600;">
                {{ $bookings->total() }} Bookings
            </span>

        </div>

        <div class="booking-table-wrapper">

            <table class="booking-table">

                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Organizer</th>
                        <th>Venue</th>
                        <th>Event Type</th>
                        <th>Booking Date</th>
                        <th>Spaces</th>
                        <th>Guests</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($bookings as $booking)

                        @php
                            $firstDetail = $booking->details
                                ->sortBy('start_at')
                                ->first();

                            $eventTypes = $booking->details
                                ->pluck('eventType.name')
                                ->filter()
                                ->unique()
                                ->implode(', ');

                            $totalGuests = $booking->details->sum('guest_count');

                            $totalPrice = $booking->details->sum('total_price');
                        @endphp

                        <tr>

                            <td>
                                <strong>#{{ $booking->id }}</strong>
                            </td>

                            <td>
                                {{ $booking->customer->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $booking->venue->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $eventTypes ?: 'N/A' }}
                            </td>

                            <td>
                                {{ $firstDetail?->start_at?->format('d M Y') ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $booking->details->count() }}
                            </td>

                            <td>
                                {{ $totalGuests }}
                            </td>

                            <td>
                                {{ number_format((float) $totalPrice, 2) }}
                            </td>

                            <td>
                                <span class="booking-status status-{{ $booking->status }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.bookings.show', $booking->id) }}"
                                    class="booking-link">
                                    View Details
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="booking-empty">
                                No bookings found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="booking-pagination">
            {{ $bookings->links() }}
        </div>

    </div>

</div>

@endsection
