<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #{{ $booking->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/venue-details.css') }}">
</head>
<body  class="venue-details">
    <main class="venue-details__content>
        <h1>Booking #{{ $booking->id }}</h1>

        <p>Venue: {{ $booking->venue?->name ?? 'Venue unavailable' }}</p>
        <p>Status: {{ ucfirst($booking->status) }}</p>

        <section>
            <h2>Booked Spaces</h2>

            @forelse ($booking->details as $detail)
                <article>
                    <h3>{{ $detail->space?->name ?? 'Space unavailable' }}</h3>

                    @if ($detail->spaceLayout)
                        <p>Layout: {{ $detail->spaceLayout->name }}</p>
                        <p>Layout capacity: {{ $detail->spaceLayout->capacity }}</p>
                    @endif

                    @if ($detail->eventType)
                        <p>Event type: {{ $detail->eventType->name }}</p>
                    @endif

                    <p>Start: {{ $detail->start_at?->format('d M Y, H:i') }}</p>
                    <p>End: {{ $detail->end_at?->format('d M Y, H:i') }}</p>
                    <p>Guests: {{ $detail->guest_count }}</p>

                    @if ($detail->pricing_type === 'package')
                        <p>Package: {{ $detail->package?->name ?? 'Package unavailable' }}</p>

                        @if ($detail->package && $detail->package->items->isNotEmpty())
                            <h4>Included Services</h4>

                            <ul>
                                @foreach ($detail->package->items as $item)
                                    <li>{{ $item->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif ($detail->pricing_type === 'hourly')
                        <p>Hourly pricing: {{ $detail->unit_price }} JOD per hour</p>
                    @endif

                    <p>Price: {{ $detail->total_price }} JOD</p>
                </article>
            @empty
                <p>No booking details are available.</p>
            @endforelse
        </section>

        <section>
    <h2>Payment Summary</h2>

    <dl>
        <dt>Booking total</dt>
        <dd>{{ number_format($totalAmount, 2) }} JOD</dd>

        <dt>Total paid</dt>
        <dd>{{ number_format($paidAmount, 2) }} JOD</dd>

        <dt>Remaining amount</dt>
        <dd>{{ number_format($remainingAmount, 2) }} JOD</dd>
    </dl>

    <h3>Payment History</h3>

    @if ($booking->payments->isEmpty())
        <p>No payments recorded yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Amount</th>
                    <th scope="col">Method</th>
                    <th scope="col">Status</th>
                    <th scope="col">Paid on</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->payments as $payment)
                    <tr>
                        <td>{{ number_format((float) $payment->amount, 2) }} JOD</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>{{ ucfirst($payment->status) }}</td>
                        <td>{{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
<section>
    <h2>Booking Review</h2>

    @if ($booking->review)
        <p>Rating: {{ $booking->review->rating }} / 5</p>

        @if ($booking->review->comment)
            <p>{{ $booking->review->comment }}</p>
        @endif
    @else
        <p>No review has been submitted for this booking.</p>
    @endif
</section>
    </main>
</body>
</html><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #{{ $booking->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body>
    <main>
        <h1>Booking #{{ $booking->id }}</h1>

        <p>Venue: {{ $booking->venue?->name ?? 'Venue unavailable' }}</p>
        <p>Status: {{ ucfirst($booking->status) }}</p>

        <section>
            <h2>Booked Spaces</h2>

            @forelse ($booking->details as $detail)
                <article>
                    <h3>{{ $detail->space?->name ?? 'Space unavailable' }}</h3>

                    @php
                        $image = $detail->space?->images
                            ->filter(fn ($image) => \Illuminate\Support\Str::startsWith($image->image_url, 'https://'))
                            ->sortByDesc('is_primary')
                            ->first();
                    @endphp

                    @if ($image)
                        <img src="{{ $image->image_url }}"
                             alt="{{ $detail->space->name }}"
                             width="220" loading="lazy">
                    @endif

                    @if ($detail->spaceLayout)
                        <p>Layout: {{ $detail->spaceLayout->name }}</p>
                        <p>Layout capacity: {{ $detail->spaceLayout->capacity }}</p>
                    @endif

                    @if ($detail->eventType)
                        <p>Event type: {{ $detail->eventType->name }}</p>
                    @endif

                    <p>Start: {{ $detail->start_at?->format('d M Y, H:i') }}</p>
                    <p>End: {{ $detail->end_at?->format('d M Y, H:i') }}</p>
                    <p>Guests: {{ $detail->guest_count }}</p>

                    @if ($detail->pricing_type === 'package')
                        <p>Package: {{ $detail->package?->name ?? 'Package unavailable' }}</p>

                        @if ($detail->package && $detail->package->items->isNotEmpty())
                            <h4>Included Services</h4>

                            <ul>
                                @foreach ($detail->package->items as $item)
                                    <li>{{ $item->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif ($detail->pricing_type === 'hourly')
                        <p>Hourly pricing: {{ $detail->unit_price }} JOD per hour</p>
                    @endif

                    <p>Price: {{ $detail->total_price }} JOD</p>
                </article>
            @empty
                <p>No booking details are available.</p>
            @endforelse
        </section>

        <section>
            <h2>Payment Summary</h2>

            <dl>
                <dt>Booking total</dt>
                <dd>{{ number_format($totalAmount, 2) }} JOD</dd>

                <dt>Total paid</dt>
                <dd>{{ number_format($paidAmount, 2) }} JOD</dd>

                <dt>Remaining amount</dt>
                <dd>{{ number_format($remainingAmount, 2) }} JOD</dd>
            </dl>

            <h3>Payment History</h3>

            @if ($booking->payments->isEmpty())
                <p>No payments recorded yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">Method</th>
                            <th scope="col">Status</th>
                            <th scope="col">Paid on</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->payments as $payment)
                            <tr>
                                <td>{{ number_format((float) $payment->amount, 2) }} JOD</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td>{{ ucfirst($payment->status) }}</td>
                                <td>{{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section>
            <h2>Booking Review</h2>

            @if ($booking->review)
                <p>Rating: {{ $booking->review->rating }} / 5</p>

                @if ($booking->review->comment)
                    <p>{{ $booking->review->comment }}</p>
                @endif
            @else
                <p>No review has been submitted for this booking.</p>
            @endif
        </section>
    </main>
</body>
</html>