@extends('layouts.owner')

@section('title', 'Edit Venue')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-venues.css') }}">
@endpush

@section('content')
<div class="venue-page">
        <h1>Edit Venue</h1>

        <form class="admin-edit-form owner-editor venue-card venue-form" method="POST" action="{{ route('owner.venues.update', $venue) }}">
            @csrf
            @method('PATCH')

            <div>
                <label for="name">Venue name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $venue->name) }}"
                    maxlength="255"
                    required
                >
                @error('name')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="address">Address</label>
                <input
                    id="address"
                    name="address"
                    type="text"
                    value="{{ old('address', $venue->address) }}"
                    maxlength="255"
                    required
                >
                @error('address')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description">Description</label>
                <textarea id="description" name="description" maxlength="5000">{{ old('description', $venue->description) }}</textarea>
                @error('description')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <x-admin.edit-actions :back-url="route('owner.venues.index')" back-label="Back to My Venues" save-label="Save changes" />
        </form>



@if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif


@error('delete_image')
    <p role="alert">{{ $message }}</p>
@enderror

<section class="venue-card">
    <h2>Current venue images</h2>

    <div class="venue-gallery">
        @forelse ($venue->images as $image)
            <figure>
                <img src="{{ $image->image_url }}"
                     alt="Photo of {{ $venue->name }}"
                     loading="lazy">

               <figcaption>
    @if ($image->is_primary)
        <strong>Primary photo</strong>
    @else
        <form method="POST"
              action="{{ route('owner.venues.images.primary', [$venue, $image->id]) }}">
            @csrf
            @method('PATCH')
            <button class="admin-button admin-button--outline" type="submit">Make primary</button>
        </form>
    @endif
</figcaption>


@if ($venue->images->count() > 1)
    <form data-delete-message="Delete this image?" data-delete-action="Delete" method="POST"
          action="{{ route('owner.venues.images.destroy', [$venue, $image->id]) }}"
          onsubmit="return confirm('Delete this image?')">
        @csrf
        @method('DELETE')
        <button class="admin-button admin-button--danger" type="submit">Delete image</button>
    </form>
@endif


            </figure>
        @empty
            <p>No images have been uploaded yet.</p>
        @endforelse
    </div>
</section>


          <form class="admin-edit-form owner-editor venue-card venue-form" method="POST" action="{{ route('owner.venues.images.store', $venue) }}"
                  enctype="multipart/form-data">
                <h2>Add venue image</h2>
                @csrf
            @php($remainingImages = max(0, 5 - $venue->images->count()))

@if ($remainingImages > 0)
    <label for="images">You can add {{ $remainingImages }} more image(s).</label>
    <input type="file" id="images" name="images[]"
           data-max-files="{{ $remainingImages }}"
           accept="image/jpeg,image/png,image/webp" multiple required>
@else
    <p>This venue has reached the 5-image limit.</p>
@endif



<div id="venue-image-previews" class="venue-gallery"
     role="group" aria-label="Selected image previews"></div>
       <script data-venue-preview src="{{ asset('js/venue-image-preview.js') }}" defer></script>

@error('images')
    <p role="alert">{{ $message }}</p>
@enderror

@error('images.*')
    <p role="alert">{{ $message }}</p>
@enderror
              <button class="admin-button admin-button--primary admin-button--page" type="submit" @disabled($remainingImages === 0)>Upload images</button>
            </form>
    </div>
@endsection
