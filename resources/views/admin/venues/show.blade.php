@extends('layouts.admin')

@section('title', 'Venue Details')
@section('hide-layout-heading', '1')

@section('content')
<div>
        <a href="{{ route('admin.venues.index') }}">Back to Manage Venues</a>

        <h1>{{ $venue->name }}</h1>

        <dl>
            <dt>Owner</dt>
            <dd>{{ $venue->owner?->name ?? 'Unknown owner' }}</dd>

            <dt>Address</dt>
            <dd>{{ $venue->address }}</dd>

            <dt>Status</dt>
            <dd>{{ ucfirst($venue->status) }}</dd>

            <dt>Description</dt>
            <dd>{{ $venue->description ?: 'No description provided.' }}</dd>
        </dl>



        <section>
    <h2>Venue photos</h2>

    @forelse ($venue->images as $image)
        <figure>
            <img src="{{ $image->image_url }}"
                 alt="Photo of {{ $venue->name }}"
                 width="240"
                 loading="lazy">

            @if ($image->is_primary)
                <figcaption><strong>Primary photo</strong></figcaption>
            @endif
        </figure>
    @empty
        <p>No photos have been uploaded for this venue.</p>
    @endforelse
</section>



        @if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif

@error('review')
    <p role="alert">{{ $message }}</p>
@enderror

@if ($venue->status === 'pending')
    <form method="POST" action="{{ route('admin.venues.approve', $venue) }}"
          onsubmit="return confirm('Approve this venue?')">
        @csrf
        @method('PATCH')

        <button type="submit">Approve</button>
    </form>

    <form method="POST" action="{{ route('admin.venues.reject', $venue) }}"
          onsubmit="return confirm('Reject this venue?')">
        @csrf
        @method('PATCH')

        <button type="submit">Reject</button>
    </form>
@endif



        <h2>Spaces</h2>


        @error('status')
    <p role="alert">{{ $message }}</p>
@enderror

@if (in_array($venue->status, ['approved', 'inactive'], true))
    <form method="POST" action="{{ route('admin.venues.status', $venue) }}"
          onsubmit="return confirm('Change the status of this venue?')">
        @csrf
        @method('PATCH')

        <button type="submit" name="status"
                value="{{ $venue->status === 'approved' ? 'inactive' : 'approved' }}">
            {{ $venue->status === 'approved' ? 'Deactivate' : 'Activate' }}
        </button>
    </form>
@endif

        <ul>
            @forelse ($venue->spaces as $space)
                <li>{{ $space->name }} (capacity: {{ $space->capacity }})</li>
            @empty
                <li>No spaces have been added to this venue.</li>
            @endforelse
        </ul>
    </div>
@endsection
