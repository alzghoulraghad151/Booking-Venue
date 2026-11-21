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
        <select id="event-type-name" name="name" required
                @error('name') aria-invalid="true" aria-describedby="event-type-name-error" @enderror>
            <option value="">Choose an approved type</option>
            @foreach ($approvedNames as $name)
                <option value="{{ $name }}" @selected(old('name', $eventType->name) === $name)>{{ $name }}</option>
            @endforeach
        </select>
        @error('name')
            <p id="event-type-name-error" class="event-types-error" role="alert">{{ $message }}</p>
        @enderror

        <label for="event-type-description">Description (optional)</label>
        <textarea id="event-type-description" name="description" rows="4" maxlength="10000"
                  @error('description') aria-invalid="true" aria-describedby="event-type-description-error" @enderror>{{ old('description', $eventType->description) }}</textarea>
        @error('description')
            <p id="event-type-description-error" class="event-types-error" role="alert">{{ $message }}</p>
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
