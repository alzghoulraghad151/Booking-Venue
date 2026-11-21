@extends('layouts.owner')

@section('title', 'Space Layouts')
@section('hide-layout-heading', '1')

@section('content')
    <x-admin.list-heading title="Space Layouts" description="Manage seating arrangements, capacities, and event types."
        :action-url="route('owner.space-layouts.create')" action-label="Add space layout" />

    <x-owner.filter-panel form-id="owner-space-layouts-filters" :action="route('owner.space-layouts.index')">
        <div>
            <label for="search">Layout Name</label>
            <input id="search" name="search" type="search" maxlength="100"
                   value="{{ $search }}">
        </div>

        <div>
            <label for="space_id">Space</label>
            <select id="space_id" name="space_id">
                <option value="">All spaces</option>

                @foreach ($spaces as $space)
                    <option value="{{ $space->id }}"
                            @selected((string) $spaceId === (string) $space->id)>
                        {{ $space->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-owner.filter-panel>

    @error('layout')
        <p role="alert">{{ $message }}</p>
    @enderror

    <div class="admin-list-panel">
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="Space Layouts table">
        <table class="dashboard-table">
        <thead>
            <tr>
                <th scope="col">Layout Name</th>
                <th scope="col">Space</th>
                <th scope="col">Capacity</th>
                <th scope="col">Event Types</th>
                <th scope="col" class="admin-table-actions">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($layouts as $layout)
                <tr>
                    <td>{{ $layout->name }}</td>
                    <td>{{ $layout->space->name }}</td>
                    <td>{{ $layout->capacity }}</td>
                    <td>{{ $layout->eventTypes->pluck('name')->join(', ') ?: 'No event types' }}</td>
                    <td class="admin-table-actions">
                        <div class="admin-table-action-group">
                            <a class="admin-table-icon" href="{{ route('owner.space-layouts.show', ['id' => $layout->id]) }}" aria-label="View" title="View"><x-admin.table-action-icon name="view" /></a>
                        <a data-edit-dialog class="admin-table-icon" href="{{ route('owner.space-layouts.edit', ['id' => $layout->id]) }}" aria-label="Edit" title="Edit"><x-admin.table-action-icon name="edit" /></a>

                        <form data-delete-message="Delete this space layout?" data-delete-action="Delete" onsubmit="return confirm('Delete this space layout?')" method="POST"
                              action="{{ route('owner.space-layouts.destroy', ['id' => $layout->id]) }}">
                            @csrf
                            @method('DELETE')

                            <button class="admin-button admin-table-icon admin-table-icon--danger" type="submit" aria-label="Delete" title="Delete"><x-admin.table-action-icon name="delete" /></button>
                        </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No space layouts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
@endsection