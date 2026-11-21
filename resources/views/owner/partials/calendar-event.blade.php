@php
    $detail = $event['detail'];
    $booking = $detail->booking;
@endphp

<article class="calendar-event calendar-status--{{ $booking->status }}">
    <p><strong><a href="{{ $bookingsUrl ?? route('owner.bookings.show', $booking) }}">{{ $detail->space->name }}</a></strong></p>
    <p>
        <time datetime="{{ $event['start']->toIso8601String() }}">{{ $event['start']->format('H:i') }}</time>
        –
        <time datetime="{{ $event['end']->toIso8601String() }}">{{ $event['end']->isStartOfDay() ? '24:00' : $event['end']->format('H:i') }}</time>
    </p>
</article>
