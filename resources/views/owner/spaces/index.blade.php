<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Spaces</title>
</head>
<body>
    <main>
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
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_url) }}" alt="{{ $space->name }}" width="100">
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
                    <tr><td colspan="7">You have not added any spaces yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $spaces->links() }}
    </main>
</body>
</html>
