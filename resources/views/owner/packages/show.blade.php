@extends('layouts.owner')

@section('title', $package->name)
@section('hide-layout-heading', '1')

@section('content')
    <h1>{{ $package->name }}</h1>

    <p>{{ $package->description }}</p>
    <p>Price: {{ $package->price }} JOD</p>
    <p>Duration: {{ $package->duration_minutes }} minutes</p>
    <p>Guests: {{ $package->min_guests }}–{{ $package->max_guests }}</p>
    <p>Status: {{ $package->is_active ? 'Active' : 'Inactive' }}</p>

    <h2>Eligible Spaces</h2>
    <ul>
        @forelse ($package->items->whereNotNull('space_id') as $item)
            <li>{{ $item->space?->name ?? $item->name }}</li>
        @empty
            <li>No eligible spaces.</li>
        @endforelse
    </ul>

    <h2>Included Services</h2>
    <ul>
        @forelse ($package->items->whereNull('space_id') as $service)
            <li>{{ $service->name }}</li>
        @empty
            <li>No services included.</li>
        @endforelse
    </ul>

    <h2>Event Types</h2>
    <ul>
        @forelse ($package->eventTypes as $eventType)
            <li>{{ $eventType->name }}</li>
        @empty
            <li>No event types selected.</li>
        @endforelse
    </ul>

    <a href="{{ route('owner.packages.index') }}">Back to Packages</a>
@endsection