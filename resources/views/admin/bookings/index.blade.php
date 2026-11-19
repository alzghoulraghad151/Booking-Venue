
@extends('layouts.admin')

@section('title', 'Bookings Management')

@section('content')

<div class="booking-page">

    <p class="booking-description">
        View and monitor all customer bookings.
    </p>

    {{-- Search and Filters --}}
    <div class="booking-card">

        <h2 class="booking-section-title">Search & Filters</h2>

        <form action="{{ route('admin.bookings.index') }}" method="GET">

            <div class="booking-filters">

                {{-- Search Organizer --}}
                <div class="booking-field">
                    <label for="organizer">Organizer</label>

                    <input
                        type="text"
                        id="organizer"
                        name="organizer"
                        value="{{ request('organizer') }}"
                        placeholder="Name or email">
                </div>

                {{-- Booking Status --}}
                <div class="booking-field">
                    <label for="status">Booking Status</label>

                    <select id="status" name="status">
                        <option value="">All Statuses</option>

                        @foreach(['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $status)
                            <option
                                value="{{ $status }}"
                                {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Venue --}}
                <div class="booking-field">
                    <label for="venue_id">Venue</label>

                    <select id="venue_id" name="venue_id">
                        <option value="">All Venues</option>

                        @foreach($venues as $venue)
                            <option
                                value="{{ $venue->id }}"
                                {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                                {{ $venue->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Date --}}
                <div class="booking-field">
                    <label for="date">Booking Date</label>

                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ request('date') }}">
                </div>

                {{-- Buttons --}}
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


    {{-- Bookings Table --}}
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
                        <th>Booking Date</th>
                        <th>Spaces</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($bookings as $booking)

                        <tr>

                            {{-- ID --}}
                            <td>
                                <strong>#{{ $booking->id }}</strong>
                            </td>

                            {{-- Organizer --}}
                            <td>
                                {{ $booking->customer->name ?? 'N/A' }}
                            </td>

                            {{-- Venue --}}
                            <td>
                                {{ $booking->venue->name ?? 'N/A' }}
                            </td>

                            {{-- Booking Date --}}
                            <td>
                                @php
                                    $firstDetail = $booking->details
                                        ->sortBy('start_at')
                                        ->first();
                                @endphp

                                {{ $firstDetail?->start_at?->format('d M Y') ?? 'N/A' }}
                            </td>

                            {{-- Spaces Count --}}
                            <td>
                                {{ $booking->details->count() }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="booking-status status-{{ $booking->status }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>

                            {{-- Action --}}
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
                            <td colspan="7" class="booking-empty">
                                No bookings found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        <div class="booking-pagination">
            {{ $bookings->links() }}
        </div>

    </div>

</div>

@endsection
