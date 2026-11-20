@extends('layouts.admin')

@section('title', 'Event Types')

@section('hide-layout-heading', '1')

@section('content')
    <x-admin.list-heading title="Event Types" description="Find, add, or update the event types available for bookings." :action-url="route('admin.event-types.create')" action-label="Add event type" />

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @error('delete')
        <p role="alert" class="event-types-error">{{ $message }}</p>
    @enderror

    <form action="{{ route('admin.event-types.index') }}" method="GET">
        <label for="event-type-search">Search by name</label>
        <input id="event-type-search" name="search" value="{{ $search }}" maxlength="100">
        <button class="admin-button admin-button--outline" type="submit">Search</button>
        @if ($search !== '')
            <a class="admin-button admin-button--text" href="{{ route('admin.event-types.index') }}">Clear</a>
        @endif
    </form>

    <section class="dashboard-panel" aria-label="Manage event types">
        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="Event types table">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col" class="admin-table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eventTypes as $eventType)
                        <tr>
                            <th scope="row">{{ $eventType->name }}</th>
                            <td class="admin-table-actions">
                                <div class="admin-table-action-group">
                                    <a class="admin-table-icon" href="{{ route('admin.event-types.edit', $eventType) }}" aria-label="Edit {{ $eventType->name }}" title="Edit">
                                        <x-admin.table-action-icon name="edit" />
                                    </a>
                                    <details class="event-types-delete">
                                        <summary class="admin-table-icon admin-table-icon--danger" aria-label="Delete {{ $eventType->name }}" title="Delete">
                                            <x-admin.table-action-icon name="delete" />
                                        </summary>
                                        <form action="{{ route('admin.event-types.destroy', $eventType) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-button admin-button--danger admin-button--small event-types-error">Confirm delete: {{ $eventType->name }}</button>
                                        </form>
                                    </details>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">{{ $search === '' ? 'No event types yet.' : 'No event types match your search.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
