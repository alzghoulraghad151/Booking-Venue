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
    </div>

@endsection
