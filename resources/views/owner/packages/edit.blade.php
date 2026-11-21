@extends('layouts.owner')

@section('title', 'Edit Package')
@section('hide-layout-heading', '1')

@section('content')
    <h1>Edit Package</h1>

    <form class="admin-edit-form owner-editor" method="POST" action="{{ route('owner.packages.update', ['id' => $package->id]) }}">
        @csrf
        @method('PATCH')

        <div>
            <label for="name">Package Name</label>
            <input id="name" name="name" type="text" maxlength="255"
                   value="{{ old('name', $package->name) }}" required>
        </div>

        <div>
            <label for="price">Price (JOD)</label>
            <input id="price" name="price" type="number" min="0.01" step="0.01"
                   value="{{ old('price', $package->price) }}" required>
        </div>

        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ old('description', $package->description) }}</textarea>
        </div>

        <div>
            <label for="duration_minutes">Duration (minutes)</label>
            <input id="duration_minutes" name="duration_minutes" type="number" min="1"
                   value="{{ old('duration_minutes', $package->duration_minutes) }}" required>
        </div>

        <div>
            <label for="min_guests">Minimum Guests</label>
            <input id="min_guests" name="min_guests" type="number" min="1"
                   value="{{ old('min_guests', $package->min_guests) }}" required>
        </div>

        <div>
            <label for="max_guests">Maximum Guests</label>
            <input id="max_guests" name="max_guests" type="number" min="1"
                   value="{{ old('max_guests', $package->max_guests) }}" required>
        </div>

        <fieldset aria-describedby="space_ids_hint">
            <legend>Eligible Spaces</legend>

            @forelse ($spaces as $space)
                <div>
                    <label>
                        <input type="checkbox" name="space_ids[]" value="{{ $space->id }}"
                               @checked(in_array($space->id, (array) old('space_ids', $errors->any() ? [] : $selectedSpaceIds)))>
                        {{ $space->name }}
                    </label>
                </div>
            @empty
                <p>No spaces available. Add a space first.</p>
            @endforelse

            <p id="space_ids_hint">
                Select at least one eligible space to make this package bookable.
            </p>

            @error('space_ids')
                <p role="alert">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="event_type_ids">Event Types</label>
            <select id="event_type_ids" name="event_type_ids[]" multiple>
                @foreach ($eventTypes as $eventType)
                    <option value="{{ $eventType->id }}"
                            @selected(in_array($eventType->id, (array) old('event_type_ids', $errors->any() ? [] : $selectedEventTypeIds)))>
                        {{ $eventType->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <fieldset>
            <legend>Included Services</legend>

            @foreach ($services as $service)
                <div>
                    <label for="service_{{ $service->id }}">Service name</label>
                    <input id="service_{{ $service->id }}" type="text"
                           name="services[{{ $service->id }}][name]"
                           value="{{ old('services.' . $service->id . '.name', $service->name) }}"
                           maxlength="255">

                    <label>
                        <input type="checkbox"
                               name="services[{{ $service->id }}][remove]"
                               value="1"
                               @checked(old('services.' . $service->id . '.remove'))>
                        Remove this service
                    </label>
                </div>
            @endforeach

            <div>
                <label for="new_service">Add a service</label>
                <input id="new_service" name="services[new][name]" type="text"
                       value="{{ old('services.new.name') }}" maxlength="255">
            </div>
        </fieldset>

        <x-admin.edit-actions :back-url="route('owner.packages.index')" back-label="Cancel" save-label="Save package" />
    </form>
@endsection