@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="dashboard-stats">
        <article>
            <h2>Total Users</h2>
            <p>{{ $totalUsers }}</p>
        </article>

        <article>
            <h2>Total Venues</h2>
            <p>{{ $totalVenues }}</p>
        </article>

        <article>
           <h2>Total Bookings</h2>
           <p>{{ $totalBookings }}</p>
        </article>

        <article>
           <h2>Venue Owners</h2>
           <p>{{ $totalOwners }}</p>
        </article>

        <article>
           <h2>Pending Venues</h2>
           <p>{{ $pendingVenues }}</p>
        </article>

        <article>
          <h2>Pending Bookings</h2>
          <p>{{ $pendingBookings }}</p>
        </article>

    </div>

    <section>
        <h2>Recent Bookings</h2>

        <table>
            <thead>
                <tr>
                    <th scope="col">Booking #</th>
                    <th scope="col">Organizer</th>
                    <th scope="col">Venue</th>
                    <th scope="col">Requested On</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentBookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>{{ $booking->customer?->name ?? '—' }}</td>
                        <td>{{ $booking->venue?->name ?? '—' }}</td>
                        <td>{{ $booking->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>{{ ucfirst($booking->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No bookings yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section>
    <h2>Venues Awaiting Review</h2>

    <ul>
        @forelse ($venuesAwaitingReview as $venue)
            <li>{{ $venue->name }}</li>
        @empty
            <li>No venues awaiting review.</li>
        @endforelse
    </ul>
</section>

@endsection
