<h1>Packages</h1>

<a href="{{ route('owner.packages.create') }}">Add Package</a>

<form method="GET" action="{{ route('owner.packages.index') }}">
    <label for="search">Package name</label>
    <input type="search" id="search" name="search"
           value="{{ $search }}" maxlength="255">

    <label for="status">Status</label>
    <select id="status" name="status">
        <option value="">All statuses</option>
        <option value="active" @selected($status === 'active')>Active</option>
        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
    </select>

    <button type="submit">Filter</button>
    <a href="{{ route('owner.packages.index') }}">Clear filters</a>
</form>

@error('package')
    <p role="alert">{{ $message }}</p>
@enderror

<table>
    <thead>
        <tr>
            <th scope="col">Package Name</th>
            <th scope="col">Price</th>
            <th scope="col">Duration</th>
            <th scope="col">Event Types</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($packages as $package)
            <tr>
                <td>{{ $package->name }}</td>
                <td>{{ $package->price }} JOD</td>
                <td>{{ $package->duration_minutes }} min</td>
                <td>{{ $package->eventTypes->pluck('name')->join(', ') ?: '—' }}</td>
                <td>{{ $package->is_active ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('owner.packages.show', ['id' => $package->id]) }}">View</a>
                    <a href="{{ route('owner.packages.edit', ['id' => $package->id]) }}">Edit</a>

                    <form method="POST"
                          action="{{ route('owner.packages.status.update', ['id' => $package->id]) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit" name="is_active" value="{{ $package->is_active ? 0 : 1 }}">
                            {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('owner.packages.destroy', ['id' => $package->id]) }}"
                          onsubmit="return confirm('Delete this package?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit">Delete</button>
                    </form>
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