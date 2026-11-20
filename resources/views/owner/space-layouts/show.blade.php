@extends('layouts.owner')

@section('title', $layout->name)
@section('hide-layout-heading', '1')

@section('content')
    <h1>{{ $layout->name }}</h1>

    <p>Space: {{ $layout->space->name }}</p>
    <p>Capacity: {{ $layout->capacity }}</p>
    <p>Event Types: {{ $layout->eventTypes->pluck('name')->join(', ') ?: 'No event types' }}</p>

    <a href="{{ route('owner.space-layouts.index') }}">Back to Space Layouts</a>
@endsection