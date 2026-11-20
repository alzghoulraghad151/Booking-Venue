@extends('layouts.owner')

@section('title', 'Add Venue')
@section('hide-layout-heading', '1')

@section('content')
<div>
        <h1>Add Venue</h1>



        <form method="POST" action="{{ route('owner.venues.store') }}" enctype="multipart/form-data">
    @csrf

    <div>
        <label for="name">Venue name</label>
        <input id="name" name="name" type="text"
               value="{{ old('name') }}" maxlength="255" required>
        @error('name')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="address">Address</label>
        <input id="address" name="address" type="text"
               value="{{ old('address') }}" maxlength="255" required>
        @error('address')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description">Description</label>
        <textarea id="description" name="description"
                  maxlength="5000">{{ old('description') }}</textarea>
        @error('description')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="image">Venue image (JPEG, PNG or WebP, up to 2 MB)</label>
        <input id="image" name="image" type="file"
               accept="image/jpeg,image/png,image/webp" required>
        @error('image')
            <p role="alert">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">Save Venue</button>
</form>

       
        <a href="{{ route('owner.venues.index') }}">Back to My Venues</a>
    </div>
@endsection
