@extends('layouts.owner')

@section('title', 'Edit Venue')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-venues.css') }}">
@endpush

@section('content')
    <div class="venue-page">
        <form class="admin-edit-form owner-editor venue-editor" method="POST"
              action="{{ route('owner.venues.update', $venue ) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('owner.venues._fields', ['venue' => $venue ?? null])
            <x-admin.edit-actions :back-url="route('owner.venues.index')" save-label="Save Changes" />
        </form>
        <script data-venue-preview src="{{ asset('js/venue-image-preview.js') }}" defer></script>
    </div>
@endsection
