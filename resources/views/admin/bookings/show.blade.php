
@extends('layouts.admin')

@section('title', 'Booking Details')

@section('content')

<div class="booking-page">

    {{-- Page Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">

        <p class="booking-description">
            Complete information about booking #{{ $booking->id }}.
        </p>

        <a
            href="{{ route('admin.bookings.index') }}"
            class="booking-btn">
            Back to Bookings
        </a>

    </div>


    {{-- Booking Information --}}
    <div class="booking-card">

        <h2 class="booking-section-title">
            Booking Information
        </h2>

        <div class="booking-info-grid">

            <div>
                <div class="booking-info-label">Booking ID</div>
                <div class="booking-info-value">
                    #{{ $booking->id }}
                </div>
            </div>

            <div>
                <div class="booking-info-label">Booking Status</div>

                <span class="booking-status status-{{ $booking->status }}">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>

            <div>
                <div class="booking-info-label">Created At</div>
                <div class="booking-info-value">
                    {{ $booking->created_at?->format('d M Y') ?? 'N/A' }}
                </div>
            </div>

            <div>
                <div class="booking-info-label">Venue</div>
                <div class="booking-info-value">
                    {{ $booking->venue->name ?? 'N/A' }}
                </div>
            </div>

        </div>

    </div>


    {{-- Customer Information --}}
    <div class="booking-card">

        <h2 class="booking-section-title">
            Customer Information
        </h2>

        <div class="booking-info-grid">

            <div>
                <div class="booking-info-label">Customer Name</div>
                <div class="booking-info-value">
                    {{ $booking->customer->name ?? 'N/A' }}
                </div>
            </div>

            <div>
                <div class="booking-info-label">Email Address</div>
                <div class="booking-info-value">
                    {{ $booking->customer->email ?? 'N/A' }}
                </div>
            </div>

        </div>

    </div>


    {{-- Booking Details --}}
    <div class="booking-card">

        <h2 class="booking-section-title">
            Reserved Spaces
        </h2>

        <div class="booking-table-wrapper">

            <table class="booking-table">

                <thead>
                    <tr>
                        <th>Space</th>
                        <th>Layout</th>
                        <th>Event Type</th>
                        <th>Package</th>
                        <th>Guests</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Price</th>
                        <th>Commission</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($booking->details as $detail)

                        <tr>

                            <td>
                                {{ $detail->space->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $detail->spaceLayout->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $detail->eventType->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $detail->package->name ?? 'No Package' }}
                            </td>

                            <td>
                                {{ $detail->guest_count }}
                            </td>

                            <td>
                                {{ $detail->start_at?->format('d M Y H:i') ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $detail->end_at?->format('d M Y H:i') ?? 'N/A' }}
                            </td>

                            <td>
                                {{ number_format((float) $detail->total_price, 2) }}
                            </td>

                            <td>
                                {{ number_format((float) $detail->commission_amount, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="booking-empty">
                                No booking details found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Financial Summary --}}
    <div class="booking-card">

        <h2 class="booking-section-title">
            Financial Summary
        </h2>

        <div class="booking-info-grid">

            <div>
                <div class="booking-info-label">
                    Total Booking Amount
                </div>

                <div class="booking-info-value">
                    {{ number_format((float) $booking->details->sum('total_price'), 2) }}
                </div>
            </div>

            <div>
                <div class="booking-info-label">
                    Total Commission
                </div>

                <div class="booking-info-value">
                    {{ number_format((float) $booking->details->sum('commission_amount'), 2) }}
                </div>
            </div>

            <div>
                <div class="booking-info-label">
                    Recorded Payments
                </div>

                <div class="booking-info-value">
                    {{ number_format((float) $booking->payments->sum('amount'), 2) }}
                </div>
            </div>

        </div>

    </div>


    {{-- Payments --}}
    <div class="booking-card">

        <h2 class="booking-section-title">
            Payment Information
        </h2>

        <div class="booking-table-wrapper">

            <table class="booking-table">

                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Transaction Reference</th>
                        <th>Paid At</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($booking->payments as $payment)

                        <tr>

                            <td>
                                #{{ $payment->id }}
                            </td>

                            <td>
                                {{ number_format((float) $payment->amount, 2) }}
                            </td>

                            <td>
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </td>

                            <td>
                                <span class="booking-status status-{{ $payment->status }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $payment->transaction_reference ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $payment->paid_at?->format('d M Y H:i') ?? 'N/A' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="booking-empty">
                                No payments found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Customer Review --}}
    <div class="booking-card">

        <h2 class="booking-section-title">
            Customer Review
        </h2>

        @if($booking->review)

            <div class="booking-info-grid">

                <div>
                    <div class="booking-info-label">
                        Rating
                    </div>

                    <div class="booking-info-value">
                        {{ $booking->review->rating }} / 5
                    </div>
                </div>

                <div>
                    <div class="booking-info-label">
                        Review Date
                    </div>

                    <div class="booking-info-value">
                        {{ $booking->review->created_at?->format('d M Y') ?? 'N/A' }}
                    </div>
                </div>

            </div>

            <div style="margin-top:20px;">

                <div class="booking-info-label">
                    Customer Comment
                </div>

                <p>
                    {{ $booking->review->comment ?? 'No comment provided.' }}
                </p>

            </div>

        @else

            <p class="booking-description">
                No review has been submitted for this booking.
            </p>

        @endif

    </div>

</div>

@endsection
