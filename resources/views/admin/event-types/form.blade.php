@extends('layouts.admin')

@section('title', $eventType->exists ? 'Edit Event Type' : 'Add Event Type')

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
            @if ($eventType->exists)
                <x-admin.edit-actions :back-url="route('admin.event-types.index')" back-label="Cancel" save-label="Save event type" />
            @else
                <x-admin.edit-actions :back-url="route('admin.event-types.index')" save-label="Add event type" />
            @endif
        </div>
    </form>
@endsection
