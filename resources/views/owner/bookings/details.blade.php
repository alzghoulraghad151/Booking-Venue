@extends('layouts.owner')

@section('title', 'Booking #'.$booking->id)
@section('subtitle', 'Confirm or reject this booking for your venue.')

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

        <div class="detail-toolbar">
            <a class="button button--quiet" href="{{ route('owner.bookings.index') }}">← Back to bookings</a>
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
                    <form action="{{ route('owner.bookings.confirm', $booking) }}" method="POST" data-delete-action="Confirm booking" data-delete-tone="primary" data-delete-message="Confirm this whole booking? The organizer will then be able to pay." onsubmit="return confirm('Confirm this whole booking?');">
                        @csrf
                        @method('PATCH')
                        <button class="button button--primary" type="submit">Confirm booking</button>
                    </form>

                    <form action="{{ route('owner.bookings.reject', $booking) }}" method="POST" data-delete-action="Reject booking" data-delete-message="Reject this whole booking? This cannot be undone." onsubmit="return confirm('Reject this whole booking?');">
                        @csrf
                        @method('PATCH')
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
                    <p>Owner: {{ $owner->name }} · Created {{ $booking->created_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE)->format('d M Y, H:i') ?? '—' }}</p>
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
                            default => $booking->status === 'pending' ? 'Awaiting your decision' : 'Awaiting payment',
                        } }}
                    </strong>
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

            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Booking records">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Space</th>
                            <th>Layout</th>
                            <th>Event type</th>
                            <th>Package / pricing</th>
                            <th>Date and time</th>
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

                                <td>
                                    <span class="cell-primary">{{ $detail->package?->name ?? ucfirst($detail->pricing_type) }}</span>
                                    <span class="cell-secondary">{{ number_format((float) $detail->unit_price, 2) }} per {{ $detail->pricing_type === 'hourly' ? 'hour' : 'package' }}</span>
                                </td>

                                <td>
                                    <span class="cell-primary">{{ $detailStart?->format('d M Y') ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $detailStart?->format('H:i') ?? '—' }}–{{ $detailEnd?->format('H:i') ?? '—' }}</span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-inline">No booking details are recorded.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading panel-heading--row">
                <div>
                    <h2>Payment summary</h2>
                    <p>The organizer pays the full booking amount in one payment. Amounts are in JOD.</p>
                </div>

                <span class="result-count">Paid {{ number_format($totals['paid'], 2) }}</span>
            </div>

            <div class="info-grid info-grid--compact">
                <div class="info-item">
                    <span>Booking total</span>
                    <strong>{{ number_format($totals['amount'], 2) }}</strong>
                </div>

                <div class="info-item">
                    <span>Amount paid</span>
                    <strong>{{ number_format($totals['paid'], 2) }}</strong>
                </div>
            </div>

            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Payment">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Amount (JOD)</th>
                            <th>Charged (USD)</th>
                            <th>Reference</th>
                            <th>Paid at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($booking->payments as $payment)
                            <tr>
                                <td class="amount">{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->stripe_usd_cents !== null ? number_format($payment->stripe_usd_cents / 100, 2) : '—' }}</td>
                                <td>{{ $payment->transaction_reference ?: '—' }}</td>
                                <td>{{ $payment->paid_at?->copy()->timezone(\App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE)->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-inline">
                                        @if ($booking->status === 'pending')
                                            No payment yet. The organizer can pay after you confirm the booking.
                                        @elseif ($booking->status === 'rejected')
                                            This booking was rejected, so no payment is required.
                                        @else
                                            Waiting for the organizer to complete the payment.
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Platform Commission --}}
        <section class="panel">
            <div class="panel-heading panel-heading--row">
                <div>
                    <h2>Platform Commission</h2>
                    <p>
                        Convene deducts a commission from each booking
                        according to the agreed commission rate.
                    </p>
                </div>

                <span class="result-count">
                    {{ rtrim(rtrim(number_format($totals['commission_rate'], 2), '0'), '.') }}% Commission
                </span>
            </div>

            <div class="info-grid info-grid--compact">

                <div class="info-item">
                    <span>Booking amount</span>
                    <strong>
                        {{ number_format($totals['amount'], 2) }} JOD
                    </strong>
                </div>

                <div class="info-item">
                    <span>
                        Platform commission ({{ rtrim(rtrim(number_format($totals['commission_rate'], 2), '0'), '.') }}%)
                    </span>
                    <strong style="color: #b42318;">
                        -{{ number_format($totals['commission'], 2) }} JOD
                    </strong>
                </div>

                <div class="info-item">
                    <span>Your net earnings</span>
                    <strong style="color: #16834a;">
                        {{ number_format($totals['owner_earnings'], 2) }} JOD
                    </strong>
                </div>

                <div class="info-item">
                    <span>Payout status</span>
                    <strong>
                        {{ match ($totals['payout']) {
                            'released' => 'Transferred to you',
                            'held' => 'Held until the event is completed',
                            default => $booking->status === 'rejected' ? 'Not applicable' : 'Not paid yet',
                        } }}
                    </strong>
                </div>

            </div>

            <p class="cell-secondary" style="margin-top: 16px;">
                @if ($booking->status === 'rejected')
                    No platform commission is charged for rejected bookings.
                @elseif ($totals['payout'] === 'released')
                    The commission was deducted and your share has been transferred.
                @else
                    Commission is calculated from the total booking amount.
                    It is an estimate until the booking payment is collected.
                @endif
            </p>
        </section>
    </div>
@endsection