@extends('layouts.owner')

@section('title', 'Packages')
@section('hide-layout-heading', '1')

@section('content')
    <x-admin.list-heading title="Packages" description="Manage prices, services, and availability for your packages."
        :action-url="route('owner.packages.create')" action-label="Add package" />

    <x-owner.filter-panel form-id="owner-packages-filters" :action="route('owner.packages.index')">
        <div>
            <label for="search">Package name</label>
            <input type="search" id="search" name="search"
                   value="{{ $search }}" maxlength="255">
        </div>

        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
    </x-owner.filter-panel>

    @error('package')
        <p role="alert">{{ $message }}</p>
    @enderror

    <div class="admin-list-panel">
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="Packages table">
        <table class="dashboard-table">
        <thead>
            <tr>
                <th scope="col">Package Name</th>
                <th scope="col">Price</th>
                <th scope="col">Duration</th>
                <th scope="col">Event Types</th>
                <th scope="col">Status</th>
                <th scope="col" class="admin-table-actions">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($packages as $package)
                <tr>
                    <td>{{ $package->name }}</td>
                    <td>{{ $package->price }} JOD</td>
                    <td>{{ $package->duration_minutes }} min</td>
                    <td>{{ $package->eventTypes->pluck('name')->join(', ') ?: 'No event types' }}</td>
                    <td>{{ $package->is_active ? 'Active' : 'Inactive' }}</td>
                    <td class="admin-table-actions">
                        <div class="admin-table-action-group">
                            <a class="admin-table-icon" href="{{ route('owner.packages.show', ['id' => $package->id]) }}" aria-label="View" title="View"><x-admin.table-action-icon name="view" /></a>
                        <a data-edit-dialog class="admin-table-icon" href="{{ route('owner.packages.edit', ['id' => $package->id]) }}" aria-label="Edit" title="Edit"><x-admin.table-action-icon name="edit" /></a>

                        <form method="POST"
                              action="{{ route('owner.packages.status.update', ['id' => $package->id]) }}">
                            @csrf
                            @method('PATCH')

                            <button class="admin-button admin-table-icon" type="submit" name="is_active" value="{{ $package->is_active ? 0 : 1 }}" aria-label="{{ $package->is_active ? 'Deactivate' : 'Activate' }}" title="{{ $package->is_active ? 'Deactivate' : 'Activate' }}"><x-admin.table-action-icon :name="$package->is_active ? 'suspend' : 'activate'" /></button>
                        </form>

                        <form data-delete-message="Delete this package?" data-delete-action="Delete" method="POST"
                              action="{{ route('owner.packages.destroy', ['id' => $package->id]) }}"
                              onsubmit="return confirm('Delete this package?')">
                            @csrf
                            @method('DELETE')

                            <button class="admin-button admin-table-icon admin-table-icon--danger" type="submit" aria-label="Delete" title="Delete"><x-admin.table-action-icon name="delete" /></button>
                        </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        {{ $search !== '' || $status !== '' ? 'No packages match your filters.' : 'No packages yet.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
@endsection