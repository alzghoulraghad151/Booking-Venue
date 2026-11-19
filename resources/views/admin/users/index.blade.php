<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
</head>

<body>

    <h1>Manage Users</h1>

    <a href="{{ route('admin.users.create') }}">Create user</a>

    <form action="{{ route('admin.users.index') }}" method="GET">

        <input type="text" name="search" placeholder="Search by name, email, or phone" value="{{ $filters['search'] }}">

        <select name="role">
            <option value="">All Roles</option>
            <option value="organizer" @selected($filters['role'] === 'organizer')>
                Organizer
            </option>
            <option value="venue_owner" @selected($filters['role'] === 'venue_owner')>
                Venue Owner
            </option>
            <option value="admin" @selected($filters['role'] === 'admin')>
                Admin
            </option>
        </select>

        <select name="status">
            <option value="">All Statuses</option>
            <option value="active" @selected($filters['status'] === 'active')>
                Active
            </option>
            <option value="suspended" @selected($filters['status'] === 'suspended')>
                Suspended
            </option>
        </select>

        <select name="sort">
            <option value="created_at" @selected($filters['sort'] === 'created_at')>
                Registration Date
            </option>
            <option value="name" @selected($filters['sort'] === 'name')>
                Name
            </option>
            <option value="email" @selected($filters['sort'] === 'email')>
                Email
            </option>
            <option value="role" @selected($filters['sort'] === 'role')>
                Role
            </option>
            <option value="status" @selected($filters['sort'] === 'status')>
                Status
            </option>
        </select>

        <select name="direction">
            <option value="asc" @selected($filters['direction'] === 'asc')>
                Ascending
            </option>
            <option value="desc" @selected($filters['direction'] === 'desc')>
                Descending
            </option>
        </select>

        <select name="per_page">
            <option value="5" @selected($filters['per_page'] === '5')>
                5 users
            </option>
            <option value="10" @selected($filters['per_page'] === '10')>
                10 users
            </option>
            <option value="20" @selected($filters['per_page'] === '20')>
                20 users
            </option>
            <option value="50" @selected($filters['per_page'] === '50')>
                50 users
            </option>
        </select>

        <button type="submit">Apply Filters</button>

    </form>

    <hr>

    <table border="1" cellpadding="10" cellspacing="0">

        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registration Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? 'Not provided' }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ $user->status }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $user->id) }}">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No users found.</td>
                </tr>
            @endforelse
        </tbody>

    </table>

    <br>

    {{ $users->links() }}

</body>

</html>
