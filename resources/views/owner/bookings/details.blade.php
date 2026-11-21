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
                    <form action="{{ route('owner.bookings.confirm', $booking) }}" method="POST" onsubmit="return confirm('Confirm this whole booking?');">
                        @csrf
                        @method('PATCH')
                        <button class="button button--primary" type="submit">Confirm booking</button>
                    </form>

                    <form action="{{ route('owner.bookings.reject', $booking) }}" method="POST" onsubmit="return confirm('Reject this whole booking?');">
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

            <div class="table-scroll dashboard-table-wrap" tabindex="0" role="region" aria-label="Booking records">
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
                            <tr>
                                <td colspan="7">
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
                    {{ $totals['commission_rate'] }}% Commission
                </span>
            </div>

            <div class="info-grid info-grid--compact">

                <div class="info-item">
                    <span>Booking amount</span>
                    <strong>
                        {{ number_format((float) $totals['amount'], 2) }} JOD
                    </strong>
                </div>

                <div class="info-item">
                    <span>
                        Platform commission ({{ $totals['commission_rate'] }}%)
                    </span>
                    <strong style="color: #b42318;">
                        -{{ number_format((float) $totals['commission'], 2) }} JOD
                    </strong>
                </div>

                <div class="info-item">
                    <span>Your net earnings</span>
                    <strong style="color: #16834a;">
                        {{ number_format((float) $totals['owner_earnings'], 2) }} JOD
                    </strong>
                </div>

            </div>

            <p class="cell-secondary" style="margin-top: 16px;">
                @if (in_array($booking->status, ['cancelled', 'rejected']))
                    No platform commission is charged for cancelled or rejected bookings.
                @else
                    Commission is calculated from the total booking amount.
                    It is an estimate until the booking payment is collected.
                @endif
            </p>
        </section>
    </div>
@endsection
