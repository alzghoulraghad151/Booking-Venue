@if ($errors->any())
    <ul role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form class="admin-edit-form owner-editor" action="{{ $editing ? route('owner.spaces.update', $space) : route('owner.spaces.store') }}" method="POST" enctype="multipart/form-data">
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
    <input id="hourly_rate" type="number" name="hourly_rate" value="{{ old('hourly_rate', $space?->hourly_rate ?? '') }}" min="0" max="10000000" step="1" required>

    <label for="status">Status</label>
    <select id="status" name="status" required>
        @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $space?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>

    @if ($editing && $space->images->isNotEmpty())
        <fieldset class="owner-space-images">
            <legend>Current images</legend>
            @foreach ($space->images as $image)
                <figure class="owner-space-image">
                    <img src="{{ $image->image_url }}" alt="{{ $space->name }}" loading="lazy">
                    @if ($space->images->count() > 1)
                        <button class="owner-space-image__delete" type="submit" name="remove_images[]" value="{{ $image->id }}" aria-label="Delete image" title="Delete image" onclick="return confirm('Delete this image?')">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3" /></svg>
                        </button>
                    @else
                        <button class="owner-space-image__delete owner-space-image__delete--disabled" type="button" disabled aria-label="The last image cannot be deleted" title="At least one image must remain">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3" /></svg>
                        </button>
                    @endif
                    @if ($image->is_primary)
                        <figcaption>Primary</figcaption>
                    @endif
                </figure>
            @endforeach
            <p class="owner-space-images__hint">At least one image must remain.</p>
        </fieldset>
    @endif

    <label for="images">{{ $editing ? 'Add images' : 'Images' }}</label>
    <input id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple @required(! $editing)>
    <p>Keep 1 to 3 images per space. JPEG, PNG, or WebP, up to 5 MB each.</p>

    @if ($editing)
        <x-admin.edit-actions :back-url="route('owner.spaces.index')" back-label="Back to My Spaces" save-label="Save space" />
    @else
        <x-admin.edit-actions :back-url="route('owner.spaces.index')" save-label="Add space" />
    @endif
</form>
