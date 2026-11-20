@if ($errors->any())
    <ul role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ $editing ? route('owner.spaces.update', $space) : route('owner.spaces.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($editing)
        @method('PUT')
    @else
        <label for="venue_id">Venue</label>
        <select id="venue_id" name="venue_id" required>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" @selected((string) old('venue_id') === (string) $venue->id)>{{ $venue->name }}</option>
            @endforeach
        </select>
    @endif

    <label for="name">Name</label>
    <input id="name" name="name" value="{{ old('name', $space?->name ?? '') }}" required maxlength="255">

    <label for="description">Description</label>
    <textarea id="description" name="description" maxlength="5000">{{ old('description', $space?->description ?? '') }}</textarea>

    <label for="capacity">Capacity</label>
    <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $space?->capacity ?? 1) }}" min="1" max="100000" required>

    <label for="hourly_rate">Hourly rate</label>
    <input id="hourly_rate" type="number" name="hourly_rate" value="{{ old('hourly_rate', $space?->hourly_rate ?? '') }}" min="0" max="10000000" step="0.01" required>

    <label for="status">Status</label>
    <select id="status" name="status" required>
        @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $space?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>

    @if ($editing && $space->images->isNotEmpty())
        <fieldset>
            <legend>Current images</legend>
            @foreach ($space->images as $image)
                <label>
                    <img src="{{ $image->image_url }}" alt="{{ $space->name }}" width="120">
                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                    Remove image
                    @if ($image->is_primary)(Primary)@endif
                </label>
            @endforeach
        </fieldset>
    @endif

    <label for="images">{{ $editing ? 'Add images' : 'Images' }}</label>
    <input id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
    <p>Upload up to 10 JPEG, PNG, or WebP images, up to 5 MB each.</p>

    <button type="submit">{{ $editing ? 'Save space' : 'Create space' }}</button>
</form>
