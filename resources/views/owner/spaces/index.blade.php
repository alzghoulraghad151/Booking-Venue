@extends('layouts.owner')

@section('title', 'My Spaces')
@section('hide-layout-heading', '1')

@section('content')
    <x-admin.list-heading title="My Spaces" description="Find and manage spaces across your venues."
        :action-url="route('owner.spaces.create')" action-label="Add space" />
<div>

        @if (session('success'))
            <p role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <ul role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <x-owner.filter-panel form-id="owner-spaces-filters" :action="route('owner.spaces.index')"
            description="Find spaces by name or venue and filter by status.">
            <div>
                <label for="search">Search</label>
                <input id="search" type="search" name="search" placeholder="Search by space or venue name" value="{{ $filters['search'] }}">
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected($filters['status'] === 'active')>Active</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                    <option value="maintenance" @selected($filters['status'] === 'maintenance')>Maintenance</option>
                </select>
            </div>

            <div>
                <label for="sort">Sort by</label>
                <select id="sort" name="sort">
                    <option value="created_at" @selected($filters['sort'] === 'created_at')>Date added</option>
                    <option value="name" @selected($filters['sort'] === 'name')>Name</option>
                    <option value="capacity" @selected($filters['sort'] === 'capacity')>Capacity</option>
                    <option value="hourly_rate" @selected($filters['sort'] === 'hourly_rate')>Hourly rate</option>
                    <option value="status" @selected($filters['sort'] === 'status')>Status</option>
                </select>
            </div>

            <div>
                <label for="direction">Direction</label>
                <select id="direction" name="direction">
                    <option value="asc" @selected($filters['direction'] === 'asc')>Ascending</option>
                    <option value="desc" @selected($filters['direction'] === 'desc')>Descending</option>
                </select>
            </div>

            <div>
                <label for="per_page">Spaces per page</label>
                <select id="per_page" name="per_page">
                    <option value="5" @selected($filters['per_page'] === '5')>5 spaces</option>
                    <option value="10" @selected($filters['per_page'] === '10')>10 spaces</option>
                    <option value="20" @selected($filters['per_page'] === '20')>20 spaces</option>
                    <option value="50" @selected($filters['per_page'] === '50')>50 spaces</option>
                </select>
            </div>
        </x-owner.filter-panel>

        <div class="admin-list-panel">
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="My Spaces table">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Venue</th>
                    <th>Capacity</th>
                    <th>Hourly rate</th>
                    <th>Status</th>
                    <th scope="col" class="admin-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($spaces as $space)
                    @php($image = $space->images->firstWhere('is_primary', true) ?? $space->images->first())
                    <tr id="space-{{ $space->id }}">
                        <td>
                            @if ($image)
                                <img src="{{ $image->image_url }}" alt="{{ $space->name }}" width="100">
                            @else
                                No image
                            @endif
                        </td>
                        <td>{{ $space->name }}</td>
                        <td>{{ $space->venue->name }}</td>
                        <td>{{ $space->capacity }}</td>
                        <td>{{ number_format((float) $space->hourly_rate, 2) }}</td>
                        <td>{{ ucfirst($space->status) }}</td>
                        <td class="admin-table-actions">
                        <div class="admin-table-action-group">
                            <a data-edit-dialog class="admin-table-icon" href="{{ route('owner.spaces.edit', $space) }}" aria-label="Edit" title="Edit"><x-admin.table-action-icon name="edit" /></a>
                            <form data-delete-message="Delete this space?" data-delete-action="Delete" action="{{ route('owner.spaces.destroy', $space) }}" method="POST" onsubmit="return confirm('Delete this space?');">
                                @csrf
                                @method('DELETE')
                                <button class="admin-button admin-table-icon admin-table-icon--danger" type="submit" aria-label="Delete" title="Delete"><x-admin.table-action-icon name="delete" /></button>
                            </form>
                        </div>
                    </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No spaces match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $spaces->links('components.admin.pagination') }}
    </div>

    </div>
@endsection
