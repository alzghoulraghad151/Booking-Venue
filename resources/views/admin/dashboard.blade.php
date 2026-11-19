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
    </div>

@endsection
