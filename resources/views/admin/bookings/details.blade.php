@extends('layouts.admin')

@section('title', 'Booking #'.$booking->id)
@section('subtitle', 'Full booking, payment, commission, and review information.')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-bookings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convene-admin-bookings.css') }}">
@endpush

@section('content')
    <div class="bookings-page admin-bookings-page admin-bookings admin-bookings--details">

        <div class="ob-toolbar">
            <div class="ob-toolbar-main">
                <a class="ob-back" href="{{ route('admin.bookings.index') }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
                    Back to bookings
                </a>
                <h1>@yield('title')</h1>
                <p class="ob-subtitle">@yield('subtitle')</p>
            </div>
            <span class="status-badge status-badge--{{ $booking->status }} ob-status-lg">{{ ucfirst($booking->status) }}</span>
        </div>

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

        <section class="ob-panel" aria-label="Booking summary">
            <div class="ob-panel-head">
                <div>
                    <p class="ob-eyebrow">Booking #{{ $booking->id }}</p>
                    <h2>{{ $booking->venue?->name ?? 'Venue unavailable' }}</h2>
                    <p>Created {{ $booking->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
            </div>

            <dl class="ob-facts">
                <div class="ob-fact">
                    <dt>Organizer</dt>
                    <dd>{{ $booking->customer?->name ?? '—' }}</dd>
                </div>
                <div class="ob-fact">
                    <dt>Email</dt>
                    <dd>{{ $booking->customer?->email ?? '—' }}</dd>
                </div>
                <div class="ob-fact">
                    <dt>Venue</dt>
                    <dd>{{ $booking->venue?->name ?? '—' }}</dd>
                </div>
                <div class="ob-fact">
                    <dt>Booking status</dt>
                    <dd>{{ ucfirst($booking->status) }}</dd>
                </div>
            </dl>
        </section>

        <section class="ob-panel ob-panel--table" aria-labelledby="ab-details-title">
            <div class="ob-panel-head ob-panel-head--padded">
                <div>
                    <h2 id="ab-details-title">Booking details</h2>
                    <p>Each reserved space, event, schedule, and its commission.</p>
                </div>
            </div>

            <div class="ob-table-wrap" tabindex="0" role="region" aria-label="Reserved spaces">
                <table class="data-table ob-table ob-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Space</th>
                            <th scope="col">Layout</th>
                            <th scope="col">Event type</th>
                            <th scope="col">Package</th>
                            <th scope="col">Time</th>
                            <th scope="col">Guests</th>
                            <th scope="col" class="ob-th-end">Total</th>
                            <th scope="col" class="ob-th-end">Commission rate</th>
                            <th scope="col" class="ob-th-end">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($booking->details->sortBy('start_at') as $detail)
                            <tr>
                                <td data-label="Space" data-span="full">
                                    <span class="cell-primary">{{ $detail->space?->name ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $detail->space?->venue?->name ?? $booking->venue?->name ?? '—' }}</span>
                                </td>
                                <td data-label="Layout">{{ $detail->spaceLayout?->name ?? '—' }}</td>
                                <td data-label="Event type">{{ $detail->eventType?->name ?? '—' }}</td>
                                <td data-label="Package">{{ $detail->package?->name ?? 'Hourly' }}</td>
                                <td data-label="Time" class="ob-num">
                                    <span class="cell-primary">{{ $detail->start_at?->format('d M Y') ?? '—' }}</span>
                                    <span class="cell-secondary">{{ $detail->start_at?->format('H:i') ?? '—' }}–{{ $detail->end_at?->format('H:i') ?? '—' }}</span>
                                </td>
                                <td data-label="Guests" class="ob-num">{{ $detail->guests > 0 ? number_format($detail->guests) : '—' }}</td>
                                <td data-label="Total" class="amount ob-num ob-amount ob-td-end">{{ number_format((float) $detail->total_price, 2) }}</td>
                                <td data-label="Commission rate" class="ob-num ob-td-end">{{ number_format((float) $detail->commission_rate, 2) }}%</td>
                                <td data-label="Commission" class="amount ob-num ob-amount ob-td-end">{{ number_format((float) $detail->commission_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="ob-empty-row">
                                <td colspan="9" data-span="full"><div class="empty-inline">No booking details are recorded.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ob-totals">
                <div>
                    <span>Booking total</span>
                    <strong>{{ number_format((float) $totals['amount'], 2) }} JOD</strong>
                </div>
                <div>
                    <span>Total commission</span>
                    <strong>{{ number_format((float) $totals['commission'], 2) }} JOD</strong>
                </div>
            </div>
        </section>

        <section class="ob-panel ob-panel--table" aria-labelledby="ab-payments-title">
            <div class="ob-panel-head ob-panel-head--padded">
                <div>
                    <h2 id="ab-payments-title">Payments</h2>
                    <p>Payments are recorded against the booking.</p>
                </div>
                <span class="ob-count">Total recorded: {{ number_format((float) $totals['payments'], 2) }}</span>
            </div>

            <div class="ob-table-wrap" tabindex="0" role="region" aria-label="Payments">
                <table class="data-table ob-table ob-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">Method</th>
                            <th scope="col">Status</th>
                            <th scope="col">Reference</th>
                            <th scope="col">Paid at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($booking->payments as $payment)
                            <tr>
                                <td data-label="Amount" class="amount ob-num ob-amount">{{ number_format((float) $payment->amount, 2) }}</td>
                                <td data-label="Method">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td data-label="Status"><span class="status-badge status-badge--{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                                <td data-label="Reference" data-span="full" class="ob-wrap">{{ $payment->transaction_reference ?: '—' }}</td>
                                <td data-label="Paid at" data-span="full" class="ob-num">{{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr class="ob-empty-row">
                                <td colspan="5" data-span="full"><div class="empty-inline">No payment records yet.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ob-panel" aria-labelledby="ab-review-title">
            <div class="ob-panel-head">
                <div>
                    <h2 id="ab-review-title">Organizer review</h2>
                    <p>Reviews appear here when one exists for this booking.</p>
                </div>
            </div>

            @if ($booking->review)
                <div class="ob-review">
                    <div class="ob-review-body">
                        <div class="ob-review-score">
                            <strong>{{ number_format((int) $booking->review->rating, 0) }}</strong>
                            <span>out of 5</span>
                        </div>
                        <div class="ob-review-text">
                            <strong class="review-title">Booking rating</strong>
                            <p class="review-comment">{{ $booking->review->comment ?: 'No written comment.' }}</p>
                        </div>
                    </div>

                    <form data-delete-message="Delete this review? This cannot be undone." data-delete-action="Delete" action="{{ route('admin.bookings.review.destroy', $booking) }}" method="POST"
                          onsubmit="return confirm('Delete this review? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ob-btn ob-btn--danger">Delete review</button>
                    </form>
                </div>
            @else
                <div class="empty-inline">No review has been submitted for this booking.</div>
            @endif
        </section>
    </div>
@endsection