@extends('layouts.owner')

@section('title', 'Booking #'.$booking->id)
@section('subtitle', 'Confirm or reject this booking for your venue.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-bookings.css') }}">
@endpush

@section('content')
    @php
        $tz = \App\Http\Requests\PublicBookingDetailsRequest::TIMEZONE;
        $sortedDetails = $booking->details->sortBy('start_at');
        $firstDetail = $sortedDetails->first();
        $firstStart = $firstDetail?->start_at?->copy()->timezone($tz);
        $firstEnd = $firstDetail?->end_at?->copy()->timezone($tz);
        $rate = rtrim(rtrim(number_format($totals['commission_rate'], 2), '0'), '.');
        $paymentLabel = match ($totals['payment_state']) {
            'paid' => 'Paid',
            'not_required' => 'Not required',
            default => $booking->status === 'pending' ? 'Awaiting your decision' : 'Awaiting payment',
        };
        $payoutLabel = match ($totals['payout']) {
            'released' => 'Transferred to you',
            'held' => 'Held until the event is completed',
            default => $booking->status === 'rejected' ? 'Not applicable' : 'Not paid yet',
        };
    @endphp

    <div class="bookings-page admin-bookings-page owner-booking-page">

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

        <div class="bk-layout">
            {{-- ===== Main column ===== --}}
            <div class="bk-main">

                {{-- Overview --}}
                <section class="panel">
                    <div class="panel-heading">
                        <p class="eyebrow">BOOKING #{{ $booking->id }}</p>
                        <h2>{{ $booking->venue?->name ?? 'Venue unavailable' }}</h2>
                        <p>Owner: {{ $owner->name }} · Created {{ $booking->created_at?->copy()->timezone($tz)->format('d M Y, H:i') ?? '—' }}</p>
                    </div>

                    <dl class="bk-facts">
                        <div>
                            <dt>Organizer</dt>
                            <dd>{{ $booking->customer?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>
                                @if ($booking->customer?->email)
                                    <a href="mailto:{{ $booking->customer->email }}">{{ $booking->customer->email }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>Event date</dt>
                            <dd>
                                {{ $firstStart?->format('d M Y') ?? '—' }}
                                @if ($firstStart && $firstEnd)
                                    <small>{{ $firstStart->format('H:i') }}–{{ $firstEnd->format('H:i') }}</small>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>Total guests</dt>
                            <dd>{{ number_format($totals['guests']) }}</dd>
                        </div>
                        <div>
                            <dt>Booking status</dt>
                            <dd>{{ ucfirst($booking->status) }}</dd>
                        </div>
                        <div>
                            <dt>Payment status</dt>
                            <dd>{{ $paymentLabel }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Reserved spaces --}}
                <section class="panel">
                    <div class="panel-heading panel-heading--row">
                        <div>
                            <h2>Reserved spaces</h2>
                            <p>Review every detail before deciding on the booking.</p>
                        </div>
                        <span class="result-count">{{ number_format($booking->details->count()) }} {{ \Illuminate\Support\Str::plural('space', $booking->details->count()) }}</span>
                    </div>

                    <div class="table-scroll dashboard-table-wrap bk-stack" tabindex="0" role="region" aria-label="Reserved spaces">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Space</th>
                                    <th>Layout</th>
                                    <th>Event type</th>
                                    <th>Package / pricing</th>
                                    <th>Date and time</th>
                                    <th>Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($sortedDetails as $detail)
                                    @php
                                        $detailStart = $detail->start_at?->copy()->timezone($tz);
                                        $detailEnd = $detail->end_at?->copy()->timezone($tz);
                                    @endphp
                                    <tr>
                                        <td data-label="Space">
                                            <span class="cell-primary">{{ $detail->space?->name ?? '—' }}</span>
                                            <span class="cell-secondary">{{ $detail->space?->venue?->name ?? $booking->venue?->name ?? '—' }}</span>
                                        </td>

                                        <td data-label="Layout">
                                            <span class="cell-primary">{{ $detail->spaceLayout?->name ?? '—' }}</span>
                                            @if ($detail->spaceLayout)
                                                <span class="cell-secondary">Capacity {{ number_format((int) $detail->spaceLayout->capacity) }}</span>
                                            @endif
                                        </td>

                                        <td data-label="Event type">{{ $detail->eventType?->name ?? '—' }}</td>

                                        <td data-label="Package / pricing">
                                            <span class="cell-primary">{{ $detail->package?->name ?? ucfirst($detail->pricing_type) }}</span>
                                            <span class="cell-secondary">{{ number_format((float) $detail->unit_price, 2) }} JOD per {{ $detail->pricing_type === 'hourly' ? 'hour' : 'package' }}</span>
                                        </td>

                                        <td data-label="Date and time" class="nowrap">
                                            <span class="cell-primary">{{ $detailStart?->format('d M Y') ?? '—' }}</span>
                                            <span class="cell-secondary">{{ $detailStart?->format('H:i') ?? '—' }}–{{ $detailEnd?->format('H:i') ?? '—' }}</span>
                                        </td>

                                        <td data-label="Total" class="amount">{{ number_format((float) $detail->total_price, 2) }} JOD</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-inline">No booking details are recorded.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Payments --}}
                <section class="panel">
                    <div class="panel-heading panel-heading--row">
                        <div>
                            <h2>Payments</h2>
                            <p>The organizer pays the full booking amount in one payment.</p>
                        </div>
                        <span class="result-count">Paid {{ number_format($totals['paid'], 2) }} JOD</span>
                    </div>

                    <div class="table-scroll dashboard-table-wrap bk-stack" tabindex="0" role="region" aria-label="Payments">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                            
                                    <th>Reference</th>
                                    <th>Paid at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($booking->payments as $payment)
                                    <tr>
                                        <td data-label="Amount" class="amount">{{ number_format((float) $payment->amount, 2) }} JOD</td>
                            
                                        <td data-label="Reference">{{ $payment->transaction_reference ?: '—' }}</td>
                                        <td data-label="Paid at" class="nowrap">{{ $payment->paid_at?->copy()->timezone($tz)->format('d M Y, H:i') ?? '—' }}</td>
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
            </div>

            {{-- ===== Side column: decision + money summary ===== --}}
            <aside class="bk-aside" aria-label="Decision and earnings">

                @if ($booking->status === 'pending')
                    <section class="panel bk-decision">
                        <p class="eyebrow">OWNER DECISION</p>
                        <h2>Confirm or reject</h2>
                        <p>This decision applies to the entire booking. Confirming checks venue status, capacity, layout, package, and space availability.</p>

                        <div class="bk-decision-actions">
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

                <section class="panel bk-summary">
                    <div class="panel-heading">
                        <h2>Earnings summary</h2>
                        <p>Amounts in JOD. Convene deducts a {{ $rate }}% commission.</p>
                    </div>

                    <dl class="bk-summary-list">
                        <div>
                            <dt>Booking total</dt>
                            <dd>{{ number_format($totals['amount'], 2) }} JOD</dd>
                        </div>
                        <div>
                            <dt>Amount paid</dt>
                            <dd>{{ number_format($totals['paid'], 2) }} JOD</dd>
                        </div>
                        <div>
                            <dt>Platform commission ({{ $rate }}%)</dt>
                            <dd class="amount--negative">-{{ number_format($totals['commission'], 2) }} JOD</dd>
                        </div>
                        <div class="bk-summary-total">
                            <dt>Your net earnings</dt>
                            <dd class="amount--positive">{{ number_format($totals['owner_earnings'], 2) }} JOD</dd>
                        </div>
                        <div>
                            <dt>Payout status</dt>
                            <dd>{{ $payoutLabel }}</dd>
                        </div>
                    </dl>

                    <p class="cell-secondary admin-bookings-note">
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
            </aside>
        </div>
    </div>
@endsection