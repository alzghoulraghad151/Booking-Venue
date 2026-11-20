@if ($errors->any())
    <ul role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<div>
    <label for="space_id">Space</label>
    <select id="space_id" name="space_id" required>
        <option value="">Select a space</option>
        @foreach ($spaces as $space)
            <option value="{{ $space->id }}"
                    @selected((string) old('space_id', $layout->space_id ?? '') === (string) $space->id)>
                {{ $space->name }} (capacity: {{ $space->capacity }})
            </option>
        @endforeach
    </select>
</div>

<div>
    <label for="name">Layout Name</label>
    <input id="name" name="name" maxlength="100"
           value="{{ old('name', $layout->name ?? '') }}" required>
</div>

<div>
    <label for="capacity">Capacity</label>
    <input id="capacity" name="capacity" type="number" min="1"
           value="{{ old('capacity', $layout->capacity ?? '') }}"
           aria-describedby="capacity-hint" required>
    <p id="capacity-hint">Use the capacity shown next to the selected space as the maximum.</p>
</div>

<div>
    <label for="event_type_ids">Event Types</label>
    <select id="event_type_ids" name="event_type_ids[]" multiple required>
        @foreach ($eventTypes as $eventType)
            <option value="{{ $eventType->id }}"
                    @selected(in_array($eventType->id, (array) old('event_type_ids', isset($layout) && !$errors->any() ? $layout->eventTypes->modelKeys() : [])))>
                {{ $eventType->name }}
            </option>
        @endforeach
    </select>
</div>
