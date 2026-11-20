@extends('layouts.owner')

@section('title', 'My Spaces')
@section('hide-layout-heading', '1')

@section('content')
<div>
        <h1>My Spaces</h1>

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

        <a href="{{ route('owner.spaces.create') }}">Add space</a>

        <form action="{{ route('owner.spaces.index') }}" method="GET">
            <label for="search">Search</label>
            <input id="search" type="text" name="search" placeholder="Search by space or venue name" value="{{ $filters['search'] }}">

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                <option value="maintenance" @selected($filters['status'] === 'maintenance')>Maintenance</option>
            </select>

            <label for="sort">Sort by</label>
            <select id="sort" name="sort">
                <option value="created_at" @selected($filters['sort'] === 'created_at')>Date added</option>
                <option value="name" @selected($filters['sort'] === 'name')>Name</option>
                <option value="capacity" @selected($filters['sort'] === 'capacity')>Capacity</option>
                <option value="hourly_rate" @selected($filters['sort'] === 'hourly_rate')>Hourly rate</option>
                <option value="status" @selected($filters['sort'] === 'status')>Status</option>
            </select>

            <label for="direction">Direction</label>
            <select id="direction" name="direction">
                <option value="asc" @selected($filters['direction'] === 'asc')>Ascending</option>
                <option value="desc" @selected($filters['direction'] === 'desc')>Descending</option>
            </select>

            <label for="per_page">Spaces per page</label>
            <select id="per_page" name="per_page">
                <option value="5" @selected($filters['per_page'] === '5')>5 spaces</option>
                <option value="10" @selected($filters['per_page'] === '10')>10 spaces</option>
                <option value="20" @selected($filters['per_page'] === '20')>20 spaces</option>
                <option value="50" @selected($filters['per_page'] === '50')>50 spaces</option>
            </select>

            <button type="submit">Apply filters</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Venue</th>
                    <th>Capacity</th>
                    <th>Hourly rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($spaces as $space)
                    @php($image = $space->images->firstWhere('is_primary', true) ?? $space->images->first())
                    <tr>
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
                        <td>
                            <a href="{{ route('owner.spaces.edit', $space) }}">Edit</a>
                            <form action="{{ route('owner.spaces.destroy', $space) }}" method="POST" onsubmit="return confirm('Delete this space?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No spaces match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $spaces->links() }}
    </div>
@endsection
