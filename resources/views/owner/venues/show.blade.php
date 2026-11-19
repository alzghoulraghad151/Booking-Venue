<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Venue Details</title>
</head>
<body>
    <main>
        <a href="{{ route('owner.venues.index') }}">Back to My Venues</a>

        <h1>{{ $venue->name }}</h1>

        <section>
            <h2>Venue images</h2>

            @foreach ($venue->images as $image)
                <img src="{{ $image->image_url }}" alt="{{ $venue->name }}" width="240" loading="lazy">
            @endforeach

            <form method="POST" action="{{ route('owner.venues.images.store', $venue) }}"
                  enctype="multipart/form-data">
                @csrf
                <label for="image">Add an image (JPEG, PNG or WebP, up to 2 MB)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" required>
                @error('image')
                    <p role="alert">{{ $message }}</p>
                @enderror
                <button type="submit">Upload image</button>
            </form>
        </section>

        <dl>
            <dt>Address</dt>
            <dd>{{ $venue->address }}</dd>

            <dt>Status</dt>
            <dd>{{ ucfirst($venue->status) }}</dd>

            <dt>Description</dt>
            <dd>{{ $venue->description ?: 'No description provided.' }}</dd>
        </dl>

        @if ($venue->status === 'pending')
            <p>Your venue is awaiting approval. Customers cannot see it yet.</p>
        @endif

        <a href="{{ route('owner.venues.edit', $venue) }}">Edit Venue</a>


        @if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif

@error('is_active_by_owner')
    <p role="alert">{{ $message }}</p>
@enderror

@if ($venue->status === 'approved')
    <p>Availability: {{ $venue->is_active_by_owner ? 'Active' : 'Inactive' }}</p>

    <form method="POST" action="{{ route('owner.venues.availability.update', $venue) }}">
        @csrf
        @method('PATCH')

        <input type="hidden" name="is_active_by_owner"
               value="{{ $venue->is_active_by_owner ? '0' : '1' }}">

        <button type="submit">
            {{ $venue->is_active_by_owner ? 'Deactivate' : 'Activate' }}
        </button>
    </form>
@endif

@error('archive')
    <p role="alert">{{ $message }}</p>
@enderror

<form method="POST" action="{{ route('owner.venues.destroy', $venue) }}"
      onsubmit="return confirm('Archive this venue?')">
    @csrf
    @method('DELETE')

    <button type="submit">Archive Venue</button>
</form>
        <h2>Spaces</h2>

        <ul>
            @forelse ($venue->spaces as $space)
                <li>{{ $space->name }} (capacity: {{ $space->capacity }})</li>
            @empty
                <li>No spaces have been added to this venue.</li>
            @endforelse
        </ul>
    </main>
</body>
</html>