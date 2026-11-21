<div>
    <label for="name">Venue name</label>
    <input id="name" name="name" value="{{ old('name', $venue?->name) }}" maxlength="255" required>
    @error('name') <p role="alert">{{ $message }}</p> @enderror
</div>
<div>
    <label for="address">Address</label>
    <select id="address" name="address" required>
        <option value="">Select a city</option>
        @foreach ($addresses as $address)
            <option value="{{ $address }}" @selected(old('address', $venue?->address) === $address)>{{ $address }}</option>
        @endforeach
    </select>
    @error('address') <p role="alert">{{ $message }}</p> @enderror
</div>
<div class="venue-form-wide">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3" maxlength="5000">{{ old('description', $venue?->description) }}</textarea>
    @error('description') <p role="alert">{{ $message }}</p> @enderror
</div>
<fieldset class="venue-form-wide venue-photo-editor" data-venue-photos>
    <legend>Venue photos</legend>
    <p class="venue-photo-hint">Choose 1–3 photos. JPEG, PNG or WebP, up to 2 MB each.</p>
    <div data-photo-cards class="venue-photo-grid">
        @foreach ($venue?->images ?? [] as $image)
            <figure data-saved-photo="{{ $image->id }}">
                <img src="{{ $image->image_url }}" alt="Venue photo {{ $loop->iteration }}">
                <label><input type="radio" name="primary_image" value="saved:{{ $image->id }}" @checked($image->is_primary)> Primary</label>
            </figure>
        @endforeach
    </div>
    <label for="venue-photos">Add photos</label>
    <input id="venue-photos" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp">
    <p data-photo-status role="status" hidden></p>
    @foreach (['images', 'images.*', 'primary_image', 'removed_images.*'] as $field)
        @error($field) <p role="alert">{{ $message }}</p> @enderror
    @endforeach
</fieldset>
