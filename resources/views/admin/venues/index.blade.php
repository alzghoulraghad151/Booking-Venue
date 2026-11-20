@extends('layouts.admin')

@section('title', 'Manage Venues')
@section('hide-layout-heading', '1')

@push('styles')
<style>
    .venue-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .venue-status--pending  { background: #fef3c7; color: #78350f; }
    .venue-status--approved { background: #dcfce7; color: #14532d; }
    .venue-status--rejected { background: #fee2e2; color: #7f1d1d; }
    .venue-status--inactive { background: #e5e7eb; color: #374151; }
</style>
@endpush

@section('content')
<x-admin.list-heading title="Manage Venues" description="Find venues and review pending listings before approving or rejecting them." />
    <div>


        <form method="GET" action="{{ route('admin.venues.index') }}">
    <label for="search">Venue or owner name</label>
    <input id="search" name="search" type="search"
           value="{{ $search }}" maxlength="100">
    @error('search')
        <p>{{ $message }}</p>
    @enderror

    <label for="status">Status</label>
    <select id="status" name="status">
        <option value="">All statuses</option>
        <option value="pending" @selected($status === 'pending')>Pending</option>
        <option value="approved" @selected($status === 'approved')>Approved</option>
        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
    </select>
    @error('status')
        <p>{{ $message }}</p>
    @enderror

    <div class="admin-venue-filter-actions">
        <button class="admin-button admin-filter-submit" type="submit">Apply filters</button>
        <a class="admin-filter-reset" href="{{ route('admin.venues.index') }}">Reset</a>
    </div>
</form>


    <div class="admin-list-panel">
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="Venues table">
        <table>
            <thead>
                <tr>
                    <th scope="col">Venue</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Address</th>
                    <th scope="col">Registered on</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="admin-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($venues as $venue)
                    <tr>
                        <td>{{ $venue->name }}</td>
                        <td>{{ $venue->owner?->name ?? 'Unknown owner' }}</td>
                        <td>{{ $venue->address }}</td>
                        <td>{{ $venue->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
    <span class="venue-status venue-status--{{ $venue->status }}">
        {{ ucfirst($venue->status) }}
    </span>
</td>
                        <td class="admin-table-actions">
                            <a class="admin-table-icon" href="{{ route('admin.venues.show', $venue) }}" aria-label="View {{ $venue->name }}" title="View">
                                    <x-admin.table-action-icon name="view" />
                                </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No venues found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $venues->links('components.admin.pagination') }}
    </div>
    </div>
@endsection
