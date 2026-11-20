@extends('layouts.bookings')

@section('title', 'Owner bookings')
@section('heading', 'Bookings')
@section('subtitle', 'Review requests for a venue owner and make booking decisions.')

@section('content')
    <section class="panel owner-context">
        <div class="panel-heading">
            <div>
                <h2>Choose a venue owner</h2>
                <p>This test build has no sign-in. Select an existing venue owner to view their bookings.</p>
            </div>
        </div>
        <form class="filter-grid owner-filter-grid" action="{{ route('owner.bookings.index') }}" method="GET">
            <div class="field">
                <label for="owner_id">Venue owner</label>
                <select id="owner_id" name="owner_id" required>
                    <option value="">Select a venue owner</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) $ownerId === (string) $owner->id)>
                            {{ $owner->name }} · #{{ $owner->id }}
                        </option>
                    @endforeach
                </select>
                @if ($owners->isEmpty())
                    <small class="field-hint">No venue owners are in the database yet.</small>
                @endif
            </div>
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
                <label for="date">Event date</label>
                <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}">
            </div>
            <div class="filter-actions">
                <button class="button button--primary" type="submit">View bookings</button>
                <a class="button button--quiet" href="{{ route('owner.bookings.index') }}">Reset</a>
            </div>
        </form>
    </section>

    @if ($selectedOwner)
        <section class="owner-welcome">
            <div class="owner-avatar" aria-hidden="true">{{ mb_substr($selectedOwner->name, 0, 1) }}</div>
            <div>
                <span class="eyebrow">CURRENT TEST OWNER</span>
                <strong>{{ $selectedOwner->name }}</strong>
            </div>
            <span class="owner-id">User #{{ $selectedOwner->id }}</span>
        </section>
    @endif

    <section class="panel">
        <div class="panel-heading panel-heading--row">
            <div>
                <h2>{{ $selectedOwner ? 'Your venue bookings' : 'Select an owner to continue' }}</h2>
                <p>{{ $selectedOwner ? 'Booking status is shared by all details in this booking.' : 'No bookings are shown until an owner is selected.' }}</p>
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
                        <th>Event date</th>
                        <th>Spaces</th>
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
                        @endphp
                        <tr>
                            <td><a class="booking-id" href="{{ route('owner.bookings.show', ['booking' => $booking, 'owner_id' => $ownerId]) }}">#{{ $booking->id }}</a></td>
                            <td>
                                <span class="cell-primary">{{ $booking->customer?->name ?? '—' }}</span>
                                <span class="cell-secondary">{{ $booking->customer?->email ?? 'No email' }}</span>
                            </td>
                            <td>{{ $booking->venue?->name ?? '—' }}</td>
                            <td>{{ $firstDetail?->start_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ number_format($booking->details_count) }}</td>
                            <td>{{ number_format((int) $booking->details->sum('guest_count')) }}</td>
                            <td class="amount">{{ number_format((float) $booking->details->sum('total_price'), 2) }}</td>
                            <td><span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            <td><a class="table-link" href="{{ route('owner.bookings.show', ['booking' => $booking, 'owner_id' => $ownerId]) }}">Review</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <span class="empty-mark" aria-hidden="true">E</span>
                                    <strong>{{ $selectedOwner ? 'No bookings found' : 'Choose a venue owner' }}</strong>
                                    <span>{{ $selectedOwner ? 'Add bookings to the database or change your filters.' : 'The list will be filtered to venues owned by the selected user.' }}</span>
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
