<h1>Add Package</h1>

<form method="POST" action="{{ route('owner.packages.store') }}">
    @csrf

    <div>
        <label for="name">Package Name</label>
        <input id="name" name="name" type="text" maxlength="255" required>
    </div>

    <div>
        <label for="description">Description</label>
        <textarea id="description" name="description"></textarea>
    </div>

    <div>
        <label for="price">Price (JOD)</label>
        <input id="price" name="price" type="number" min="0.01" step="0.01" required>
    </div>

    <div>
        <label for="duration_minutes">Duration (minutes)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="1" required>
    </div>

    <div>
        <label for="min_guests">Minimum Guests</label>
        <input id="min_guests" name="min_guests" type="number" min="1" required>
    </div>

    <div>
        <label for="max_guests">Maximum Guests</label>
        <input id="max_guests" name="max_guests" type="number" min="1" required>
    </div>

    <div>
        <label for="space_ids">Eligible Spaces</label>
        <select id="space_ids" name="space_ids[]" multiple required
                aria-describedby="space_ids_hint">
            @foreach ($spaces as $space)
                <option value="{{ $space->id }}">{{ $space->name }}</option>
            @endforeach
        </select>

        <p id="space_ids_hint">
            Select at least one eligible space to make this package bookable.
        </p>

        @error('space_ids')
            <p role="alert">{{ $message }}</p>
        @enderror

        @if ($spaces->isEmpty())
            <p>No spaces available. Add a space first.</p>
        @endif
    </div>

    <div>
        <label for="event_type_ids">Event Types</label>
        <select id="event_type_ids" name="event_type_ids[]" multiple>
            @foreach ($eventTypes as $eventType)
                <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="service_name_0">Included service</label>
        <input id="service_name_0" name="services[0][name]" type="text"
               value="{{ old('services.0.name') }}" maxlength="255"
               placeholder="e.g. Coffee service">
    </div>

    <button type="submit">Save Package</button>
</form>