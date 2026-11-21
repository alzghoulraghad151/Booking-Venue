@extends('layouts.owner')

@section('title', $layout->name)
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record owner-record">
    <h1>{{ $layout->name }}</h1>

    <section class="dashboard-panel" aria-label="Layout details">
    <p>Space: {{ $layout->space->name }}</p>
    <p>Capacity: {{ $layout->capacity }}</p>
    <p>Event Types: {{ $layout->eventTypes->pluck('name')->join(', ') ?: 'No event types' }}</p>
    </section>

    <a class="admin-button admin-button--navy" href="{{ route('owner.space-layouts.index') }}">Back to Space Layouts</a>
    </div>
@endsection