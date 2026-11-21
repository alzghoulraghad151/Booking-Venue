@extends('layouts.owner')

@section('title', 'My Venues')
@section('hide-layout-heading', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/convene-venues.css') }}">
@endpush

@section('content')
    <x-admin.list-heading title="My Venues" description="Manage your venue listings and their availability."
        :action-url="route('owner.venues.create')" action-label="Add venue" />
<div class="venue-page">
    <x-owner.filter-panel form-id="owner-venues-filters" :action="route('owner.venues.index')">
        <div>
            <label for="venue-search">Venue name</label>
            <input id="venue-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="255">
        </div>
        <div>
            <label for="venue-status">Status</label>
            <select id="venue-status" name="status">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'rejected', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="venue-sort">Date added</label>
            <select id="venue-sort" name="sort">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest first</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest first</option>
            </select>
        </div>
    </x-owner.filter-panel>

        @if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif

<div class="admin-list-panel">
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="My Venues table">
        <table class="dashboard-table">
    <thead>
        <tr>
            <th scope="col">Venue</th>
            <th scope="col">Address</th>
            <th scope="col">Status</th>
            <th scope="col" class="admin-table-actions">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($venues as $venue)
            <tr>
                <th scope="row">{{ $venue->name }}</th>
                <td>{{ $venue->address }}</td>
                <td><span class="venue-status venue-status--{{ $venue->status }}">{{ ucfirst($venue->status) }}</span></td>
                <td class="admin-table-actions">
                        <div class="admin-table-action-group">
                            <a class="admin-table-icon" href="{{ route('owner.venues.show', $venue) }}" aria-label="View" title="View"><x-admin.table-action-icon name="view" /></a>
                    <a data-edit-dialog class="admin-table-icon" href="{{ route('owner.venues.edit', $venue) }}" aria-label="Edit" title="Edit"><x-admin.table-action-icon name="edit" /></a>
                        </div>
                    </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No venues match these filters.</td>
            </tr>
        @endforelse
    </tbody>
</table>
        </div>
        {{ $venues->links('components.admin.pagination') }}
    </div>

        
    </div>
@endsection
