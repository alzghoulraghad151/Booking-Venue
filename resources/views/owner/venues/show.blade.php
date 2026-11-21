@extends('layouts.owner')

@section('title', 'Venue Details')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-venues.css') }}">
@endpush

@section('content')
<div class="venue-page">
        <a class="admin-button admin-button--navy" href="{{ route('owner.venues.index') }}">Back to My Venues</a>

        <h1>{{ $venue->name }}</h1>

        <section class="venue-card">
            <h2>Venue images</h2>

            <div class="venue-gallery">
            @forelse ($venue->images as $image)
                <img src="{{ $image->image_url }}" alt="{{ $venue->name }}" width="240" loading="lazy">
            @empty
                <p>No images have been uploaded yet.</p>
            @endforelse
            </div>

          
        </section>

        <dl class="venue-card venue-details">
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

        <a class="admin-button admin-button--primary admin-button--page" href="{{ route('owner.venues.edit', $venue) }}">Edit Venue</a>


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

        <button class="admin-button admin-button--outline" type="submit">
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

    <button class="admin-button admin-button--danger" type="submit">Archive Venue</button>
</form>
        <h2>Spaces</h2>

        <ul>
            @forelse ($venue->spaces as $space)
                <li>{{ $space->name }} (capacity: {{ $space->capacity }})</li>
            @empty
                <li>No spaces have been added to this venue.</li>
            @endforelse
        </ul>
    </div>
@endsection
