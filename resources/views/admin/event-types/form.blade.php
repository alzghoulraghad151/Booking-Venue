@extends('layouts.admin')

@section('title', $eventType->exists ? 'Edit Event Type' : 'Add Event Type')

@section('subtitle', $eventType->exists
    ? 'Update the name shown wherever this event type is used.'
    : 'Add a name organizers can use to classify an event.')

@section('content')
    <form class="dashboard-panel event-types-form" method="POST"
          action="{{ $eventType->exists ? route('admin.event-types.update', $eventType) : route('admin.event-types.store') }}">
        @csrf
        @if ($eventType->exists)
            @method('PUT')
        @endif

        <label for="event-type-name">Name</label>
        <input id="event-type-name" type="text" name="name"
               value="{{ old('name', $eventType->name) }}" required maxlength="100"
               @error('name') aria-invalid="true" aria-describedby="event-type-name-error" @enderror>
        @error('name')
            <p id="event-type-name-error" class="event-types-error" role="alert">{{ $message }}</p>
        @enderror

        <div class="event-types-actions">
            <button class="admin-button admin-button--primary event-types-primary" type="submit">Save event type</button>
            <a class="admin-button admin-button--outline" href="{{ route('admin.event-types.index') }}">Cancel</a>
        </div>
    </form>
@endsection
