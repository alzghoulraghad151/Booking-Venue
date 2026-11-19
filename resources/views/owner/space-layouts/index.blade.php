<h1>Space Layouts</h1>

<a href="{{ route('owner.space-layouts.create') }}">Add Space Layout</a>

<form method="GET" action="{{ route('owner.space-layouts.index') }}">
    <label for="search">Layout Name</label>
    <input id="search" name="search" type="search" maxlength="100" value="{{ $search }}">

    <label for="space_id">Space</label>
    <select id="space_id" name="space_id">
        <option value="">All spaces</option>
        @foreach ($spaces as $space)
            <option value="{{ $space->id }}" @selected((string) $spaceId === (string) $space->id)>
                {{ $space->name }}
            </option>
        @endforeach
    </select>

    <button type="submit">Filter</button>
    <a href="{{ route('owner.space-layouts.index') }}">Clear filters</a>
</form>

@error('layout')
    <p role="alert">{{ $message }}</p>
@enderror

<table>
    <thead>
        <tr>
            <th scope="col">Layout Name</th>
            <th scope="col">Space</th>
            <th scope="col">Capacity</th>
            <th scope="col">Event Types</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($layouts as $layout)
            <tr>
                <td>{{ $layout->name }}</td>
                <td>{{ $layout->space->name }}</td>
                <td>{{ $layout->capacity }}</td>
                <td>{{ $layout->eventTypes->pluck('name')->join(', ') ?: 'ΓÇö' }}</td>
                <td>
                    <a href="{{ route('owner.space-layouts.show', ['id' => $layout->id]) }}">View</a>
                    <a href="{{ route('owner.space-layouts.edit', ['id' => $layout->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('owner.space-layouts.destroy', ['id' => $layout->id]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No space layouts found.</td></tr>
        @endforelse
    </tbody>
</table>
