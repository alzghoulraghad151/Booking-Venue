@extends('layouts.admin')

@section('title', 'Event Types')

@section('content')
    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @error('delete')
        <p role="alert" class="event-types-error">{{ $message }}</p>
    @enderror

    <section class="dashboard-panel" aria-label="Manage event types">
        <div class="dashboard-panel-header event-types-actions">
            <form action="{{ route('admin.event-types.index') }}" method="GET">
                <label for="event-type-search">Search by name</label>
                <input id="event-type-search" name="search" value="{{ $search }}" maxlength="100">
                <button type="submit">Search</button>
                @if ($search !== '')
                    <a href="{{ route('admin.event-types.index') }}">Clear</a>
                @endif
            </form>
            <a class="event-types-primary" href="{{ route('admin.event-types.create') }}">Add event type</a>
        </div>

        <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="Event types table">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Booking details</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eventTypes as $eventType)
                        <tr>
                            <th scope="row">{{ $eventType->name }}</th>
                            <td>{{ $eventType->description ?? '—' }}</td>
                            <td>{{ $eventType->booking_details_count }}</td>
                            <td>
                                <a href="{{ route('admin.event-types.edit', $eventType) }}">Edit</a>
                                <details class="event-types-delete">
                                    <summary>Delete</summary>
                                    <form action="{{ route('admin.event-types.destroy', $eventType) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="event-types-error">Confirm delete: {{ $eventType->name }}</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">{{ $search === '' ? 'No event types yet.' : 'No event types match your search.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
