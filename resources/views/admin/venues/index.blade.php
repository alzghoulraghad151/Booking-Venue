<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Venues</title>
</head>
<body>
    <main>
        <h1>Manage Venues</h1>

        <table>
            <thead>
                <tr>
                    <th scope="col">Venue</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Address</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($venues as $venue)
                    <tr>
                        <td>{{ $venue->name }}</td>
                        <td>{{ $venue->owner?->name ?? 'Unknown owner' }}</td>
                        <td>{{ $venue->address }}</td>
                        <td>{{ ucfirst($venue->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No venues yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $venues->links() }}
    </main>
</body>
</html>