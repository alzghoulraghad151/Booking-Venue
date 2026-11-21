@extends('layouts.owner')

@section('title', 'Booking Calendar')
@section('subtitle', 'Check scheduled bookings across your spaces.')

@section('content')
    <section class="dashboard-panel" aria-labelledby="calendar-filters">
        <h2 id="calendar-filters">Calendar filters</h2>

        @if ($errors->any())
            <ul class="calendar-errors" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="calendar-controls" action="{{ route('owner.calendar.index') }}" method="GET">
            <label>Date <input type="date" name="date" value="{{ $anchor->toDateString() }}"></label>
            <label>View
                <select name="view">
                    <option value="day" @selected($mode === 'day')>Day</option>
                    <option value="week" @selected($mode === 'week')>Week</option>
                    <option value="month" @selected($mode === 'month')>Month</option>
                </select>
            </label>
            <label>Space
                <select name="space_id">
                    <option value="">All my spaces</option>
                    @foreach ($spaces as $space)
                        <option value="{{ $space->id }}" @selected($spaceId === $space->id)>
                            {{ $space->name }} · {{ $space->venue->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Apply filters</button>
        </form>
    </section>

    <section class="dashboard-panel" aria-labelledby="calendar-title">
        <header class="dashboard-panel-header">
            <div>
                <h2 id="calendar-title">
                    @if ($mode === 'month')
                        {{ $start->format('F Y') }}
                    @elseif ($mode === 'day')
                        {{ $start->format('D, j M Y') }}
                    @else
                        {{ $start->format('j M Y') }} – {{ $end->subDay()->format('j M Y') }}
                    @endif
                </h2>
                <p class="dashboard-note">All times in Asia/Amman.</p>
            </div>
            <nav class="calendar-navigation" aria-label="Calendar dates">
                <a href="{{ route('owner.calendar.index', ['view' => $mode, 'date' => $previous->toDateString(), 'space_id' => $spaceId]) }}">Previous</a>
                <a href="{{ route('owner.calendar.index', ['view' => $mode, 'date' => $today->toDateString(), 'space_id' => $spaceId]) }}">Today</a>
                <a href="{{ route('owner.calendar.index', ['view' => $mode, 'date' => $end->toDateString(), 'space_id' => $spaceId]) }}">Next</a>
            </nav>
        </header>

               <ul class="calendar-legend" aria-label="Booking statuses">
            @foreach (['pending', 'confirmed', 'completed', 'rejected'] as $status)
                <li class="calendar-status calendar-status--{{ $status }}">{{ ucfirst($status) }}</li>
            @endforeach
        </ul>
        <p class="dashboard-note">Confirmed and completed bookings occupy time. Pending requests can overlap. Rejected bookings release their time.</p>

        @if ($spaces->isEmpty())
            <p>No spaces yet. Add a venue and a space to see bookings on your calendar.</p>
        @else
            <div class="calendar-days calendar-days--{{ $mode }}">
                @if ($mode === 'month')
                    @for ($offset = 1; $offset < $start->dayOfWeekIso; $offset++)
                        <div class="calendar-day-spacer" aria-hidden="true"></div>
                    @endfor
                @endif

                @foreach ($days as $day)
                    <section class="calendar-day" aria-labelledby="calendar-day-{{ $loop->index }}">
                        <h3 id="calendar-day-{{ $loop->index }}">{{ $day['date']->format('D, j M') }}</h3>
                        @forelse ($day['events'] as $event)
                            @include('owner.partials.calendar-event', ['event' => $event])
                        @empty
                            <p class="dashboard-note">No bookings.</p>
                        @endforelse
                    </section>
                @endforeach
            </div>
        @endif
    </section>
@endsection
