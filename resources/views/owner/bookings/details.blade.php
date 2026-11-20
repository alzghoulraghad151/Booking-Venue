@extends('layouts.bookings')

@section('title', 'Owner booking #'.$booking->id)
@section('heading', 'Booking review')
@section('subtitle', 'Confirm or reject this booking for your venue.')

@section('content')
    <div class="detail-toolbar">
        <a class="button button--quiet" href="{{ route('owner.bookings.index', ['owner_id' => $owner->id]) }}">← Back to bookings</a>
        <span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
    </div>

    @if ($booking->status === 'pending')
        <section class="decision-banner">
            <div>
                <span class="eyebrow">OWNER DECISION</span>
                <strong>This decision applies to the entire booking.</strong>
                <span>Confirming checks venue status, capacity, layout, package, and space availability.</span>
            </div>
            <div class="decision-actions">
                <form action="{{ route('owner.bookings.confirm', $booking) }}" method="POST" onsubmit="return confirm('Confirm this whole booking?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                    <button class="button button--primary" type="submit">Confirm booking</button>
                </form>
                <form action="{{ route('owner.bookings.reject', $booking) }}" method="POST" onsubmit="return confirm('Reject this whole booking?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                    <button class="button button--danger" type="submit">Reject booking</button>
                </form>
            </div>
        </section>
    @endif

    <section class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">BOOKING #{{ $booking->id }}</p>
                <h2>{{ $booking->venue?->name ?? 'Venue unavailable' }}</h2>
                <p>Owner: {{ $owner->name }} · Created {{ $booking->created_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span>Organizer</span>
                <strong>{{ $booking->customer?->name ?? '—' }}</strong>
            </div>
            <div class="info-item">
                <span>Email</span>
                <strong>{{ $booking->customer?->email ?? '—' }}</strong>
            </div>
            <div class="info-item">
                <span>Venue</span>
                <strong>{{ $booking->venue?->name ?? '—' }}</strong>
            </div>
            <div class="info-item">
                <span>Booking status</span>
                <strong>{{ ucfirst($booking->status) }}</strong>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Reserved spaces</h2>
                <p>Review every detail before deciding on the booking.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Space</th>
                        <th>Layout</th>
                        <th>Event type</th>
                        <th>Package / pricing</th>
                        <th>Date and time</th>
                        <th>Guests</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($booking->details->sortBy('start_at') as $detail)
                        <tr>
                            <td>
                                <span class="cell-primary">{{ $detail->space?->name ?? '—' }}</span>
                                <span class="cell-secondary">{{ $detail->space?->venue?->name ?? $booking->venue?->name ?? '—' }}</span>
                            </td>
                            <td>{{ $detail->spaceLayout?->name ?? '—' }}</td>
                            <td>{{ $detail->eventType?->name ?? '—' }}</td>
                            <td>
                                <span class="cell-primary">{{ $detail->package?->name ?? ucfirst($detail->pricing_type) }}</span>
                                <span class="cell-secondary">{{ number_format((float) $detail->unit_price, 2) }} per {{ $detail->pricing_type === 'hourly' ? 'hour' : 'package' }}</span>
                            </td>
                            <td>
                                <span class="cell-primary">{{ $detail->start_at?->format('d M Y') ?? '—' }}</span>
                                <span class="cell-secondary">{{ $detail->start_at?->format('H:i') ?? '—' }}–{{ $detail->end_at?->format('H:i') ?? '—' }}</span>
                            </td>
                            <td>{{ number_format((int) $detail->guest_count) }}</td>
                            <td class="amount">{{ number_format((float) $detail->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-inline">No booking details are recorded.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading panel-heading--row">
            <div>
                <h2>Payment summary</h2>
                <p>Payments belong to the booking, not an individual detail.</p>
            </div>
            <span class="result-count">Paid {{ number_format((float) $totals['paid'], 2) }}</span>
        </div>
        <div class="info-grid info-grid--compact">
            <div class="info-item">
                <span>Booking total</span>
                <strong>{{ number_format((float) $totals['amount'], 2) }}</strong>
            </div>
            <div class="info-item">
                <span>Payments recorded</span>
                <strong>{{ number_format((float) $booking->payments->sum('amount'), 2) }}</strong>
            </div>
            <div class="info-item">
                <span>Payment records</span>
                <strong>{{ $booking->payments->count() }}</strong>
            </div>
        </div>
    </section>
@endsection
