@extends('layouts.admin')

@section('title', 'Booking #'.$booking->id)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-bookings.css') }}">
@endpush

@section('content')
    <div class="bookings-page admin-bookings-page">
        <p class="page-subtitle">Full booking, payment, commission, and review information.</p>
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

        <div class="detail-toolbar">
            <a class="button button--quiet" href="{{ route('admin.bookings.index') }}">← Back to bookings</a>
            <span class="status-badge status-badge--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
        </div>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">BOOKING #{{ $booking->id }}</p>
                    <h2>{{ $booking->venue?->name ?? 'Venue unavailable' }}</h2>
                    <p>Created {{ $booking->created_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE)->format('d M Y, H:i') ?? '—' }}</p>
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
                <div class="info-item">
                    <span>Total guests</span>
                    <strong>{{ number_format($totals['guests']) }}</strong>
                </div>
                <div class="info-item">
                    <span>Payment status</span>
                    <strong>
                        {{ match ($totals['payment_state']) {
                            'paid' => 'Paid',
                            'not_required' => 'Not required',
                            default => $booking->status === 'pending' ? 'Awaiting owner decision' : 'Awaiting payment',
                        } }}
                    </strong>
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
            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Reserved spaces">
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
                            @php
                                $detailStart = $detail->start_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE);
                                $detailEnd = $detail->end_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE);
                            @endphp
                            <tr>
                                <td>
                                    <span class="cell-primary">{{ $detail->space?->name ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $detail->space?->venue?->name ?? $booking->venue?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="cell-primary">{{ $detail->spaceLayout?->name ?? '—' }}</span>
                                    @if ($detail->spaceLayout)
                                        <span class="cell-secondary">Capacity {{ number_format((int) $detail->spaceLayout->capacity) }}</span>
                                    @endif
                                </td>
                                <td>{{ $detail->eventType?->name ?? '—' }}</td>
                                <td>{{ $detail->package?->name ?? 'Hourly' }}</td>
                                <td>
                                    <span class="cell-primary">{{ $detailStart?->format('d M Y') ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $detailStart?->format('H:i') ?? '—' }}–{{ $detailEnd?->format('H:i') ?? '—' }}</span>
                                </td>
                                <td>{{ number_format($detail->guests) }}</td>
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
            <div class="panel-heading panel-heading--row">
                <div>
                    <h2>Payments</h2>
                    <p>
                        All payment attempts are listed. Only the successful payment is counted.
                        @if ($totals['failed_attempts'] > 0)
                            Failed attempts: {{ $totals['failed_attempts'] }}.
                        @endif
                    </p>
                </div>
                <span class="result-count">Paid {{ number_format($totals['paid'], 2) }} of {{ number_format($totals['amount'], 2) }}</span>
            </div>
            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Payments">
                <table class="data-table">
                    <thead>
                        <tr><th>Amount (JOD)</th><th>Method</th><th>Status</th><th>Charged (USD)</th><th>Reference</th><th>Paid at</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($booking->payments as $payment)
                            <tr>
                                <td class="amount">{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td><span class="status-badge status-badge--{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                                <td>{{ $payment->stripe_usd_cents !== null ? number_format($payment->stripe_usd_cents / 100, 2) : '—' }}</td>
                                <td>{{ $payment->transaction_reference ?: '—' }}</td>
                                <td>{{ $payment->paid_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE)->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-inline">No payment records yet.</div></td></tr>
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
                        <form data-delete-message="Delete this review? This cannot be undone." data-delete-action="Delete" action="{{ route('admin.bookings.review.destroy', $booking) }}" method="POST"
                              onsubmit="return confirm('Delete this review? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button button--danger">Delete review</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="empty-inline">No review has been submitted for this booking.</div>
            @endif
        </section>
    </div>
@endsection