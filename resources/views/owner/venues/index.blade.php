<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Venues</title>
</head>
<body>
    <main>
        <h1>My Venues</h1>

        @if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif


        <a href="{{ route('owner.venues.create') }}">Add Venue</a>

        <ul>
            @forelse ($venues as $venue)
                <li>
                    <strong>{{ $venue->name }}</strong>
                    <span>{{ $venue->address }}</span>
                    <span>{{ ucfirst($venue->status) }}</span>
                    <a href="{{ route('owner.venues.edit', $venue) }}">Edit</a>
                </li>
            @empty
                <li>You haven’t added any venues yet.</li>
            @endforelse
            


            
        </ul>

        
        {{ $venues->links() }}
    </main>
</body>
</html>