@php
    $detail = $event['detail'];
    $booking = $detail->booking;
@endphp

<article class="calendar-event calendar-status--{{ $booking->status }}">
    <p>
        <strong>{{ $detail->space->name }}</strong>
        <span>{{ ucfirst($booking->status) }}</span>
    </p>
    <p>
        <time datetime="{{ $event['start']->toIso8601String() }}">{{ $event['start']->format('H:i') }}</time>
        –
        <time datetime="{{ $event['end']->toIso8601String() }}">{{ $event['end']->isStartOfDay() ? '24:00' : $event['end']->format('H:i') }}</time>
    </p>
    <p>{{ $detail->space->venue?->name ?? '—' }} · {{ $detail->eventType?->name ?? '—' }}</p>
    <p>{{ $booking->customer?->name ?? '—' }} · {{ $detail->guest_count }} guests</p>
    @if ($event['overlap'])
        <p><strong>Overlapping time</strong></p>
    @endif
    @if (Route::has('owner.bookings.show'))
        <a href="{{ route('owner.bookings.show', $booking) }}">Booking #{{ $booking->id }}</a>
    @else
        <p>Booking #{{ $booking->id }}</p>
    @endif
</article>
