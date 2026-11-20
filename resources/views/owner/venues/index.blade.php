@extends('layouts.owner')

@section('title', 'My Venues')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-venues.css') }}">
@endpush

@section('content')
<div class="venue-page">
        <h1>My Venues</h1>

        @if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif


        <a class="venue-button venue-button--primary" href="{{ route('owner.venues.create') }}">Add Venue</a>
        <a href="{{ route('owner.spaces.index') }}">My Spaces</a>

<div class="venue-table">
<table>
    <thead>
        <tr>
            <th scope="col">Venue</th>
            <th scope="col">Address</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($venues as $venue)
            <tr>
                <th scope="row">{{ $venue->name }}</th>
                <td>{{ $venue->address }}</td>
                <td><span class="venue-status venue-status--{{ $venue->status }}">{{ ucfirst($venue->status) }}</span></td>
                <td>
                    <a href="{{ route('owner.venues.show', $venue) }}">View</a>
                    <a href="{{ route('owner.venues.edit', $venue) }}">Edit</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">You haven't added any venues yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>

        
        {{ $venues->links() }}
    </div>
@endsection
