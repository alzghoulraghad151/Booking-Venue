@extends('layouts.bookings')

@section('title', 'Booking #'.$booking->id)
@section('heading', 'Booking details')
@section('subtitle', 'Full booking, payment, commission, and review information.')

@section('content')
    <div class="detail-toolbar">
        <a class="button button--quiet" href="{{ route('admin.bookings.index') }}">← Back to bookings</a>
        <span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
    </div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">BOOKING #{{ $booking->id }}</p>
                <h2>{{ $booking->venue?->name ?? 'Venue unavailable' }}</h2>
                <p>Created {{ $booking->created_at?->format('d M Y, H:i') ?? '—' }}</p>
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
                <h2>Booking details</h2>
                <p>Each reserved space, event, schedule, and its commission.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Space / venue</th>
                        <th>Layout</th>
                        <th>Event type</th>
                        <th>Package</th>
                        <th>Time</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Commission rate</th>
                        <th>Commission</th>
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
                            <td>{{ $detail->package?->name ?? 'Hourly' }}</td>
                            <td>
                                <span class="cell-primary">{{ $detail->start_at?->format('d M Y') ?? '—' }}</span>
                                <span class="cell-secondary">{{ $detail->start_at?->format('H:i') ?? '—' }}–{{ $detail->end_at?->format('H:i') ?? '—' }}</span>
                            </td>
                            <td>{{ number_format((int) $detail->guest_count) }}</td>
                            <td class="amount">{{ number_format((float) $detail->total_price, 2) }}</td>
                            <td>{{ number_format((float) $detail->commission_rate, 2) }}%</td>
                            <td class="amount">{{ number_format((float) $detail->commission_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="empty-inline">No booking details are recorded.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="totals-strip">
            <div><span>Booking total</span><strong>{{ number_format((float) $totals['amount'], 2) }}</strong></div>
            <div><span>Total commission</span><strong>{{ number_format((float) $totals['commission'], 2) }}</strong></div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Payments</h2>
                <p>Payments are recorded against the booking.</p>
            </div>
            <span class="result-count">Total recorded: {{ number_format((float) $totals['payments'], 2) }}</span>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr><th>Amount</th><th>Method</th><th>Status</th><th>Reference</th><th>Paid at</th></tr>
                </thead>
                <tbody>
                    @forelse ($booking->payments as $payment)
                        <tr>
                            <td class="amount">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td><span class="status-badge status-badge--{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>{{ $payment->transaction_reference ?: '—' }}</td>
                            <td>{{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-inline">No payment records yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Organizer review</h2>
                <p>Reviews appear here when one exists for this booking.</p>
            </div>
        </div>
        @if ($booking->review)
            <div class="review-card">
                <div class="review-score">
                    <strong>{{ number_format((int) $booking->review->rating, 0) }}</strong>
                    <span>out of 5</span>
                </div>
                <div>
                    <strong class="review-title">Booking rating</strong>
                    <p class="review-comment">{{ $booking->review->comment ?: 'No written comment.' }}</p>
                </div>
            </div>
        @else
            <div class="empty-inline">No review has been submitted for this booking.</div>
        @endif
    </section>
@endsection
