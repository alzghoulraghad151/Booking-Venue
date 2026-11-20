@extends('layouts.admin')

@section('title', 'Admin - Manage Users')
@section('hide-layout-heading', '1')

@section('content')
    <x-admin.list-heading title="Manage Users" :action-url="route('admin.users.create')" action-label="Create user" />

    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif

    <div class="admin-users-toolbar">
        <form class="admin-users-filters" action="{{ route('admin.users.index') }}" method="GET">

            <input type="search" name="search" aria-label="Search users" placeholder="Name, email, or phone" value="{{ $filters['search'] }}">

            <select name="role" aria-label="Role">
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

            <select name="status" aria-label="Status">
                <option value="">All Statuses</option>
                <option value="active" @selected($filters['status'] === 'active')>
                    Active
                </option>
                <option value="suspended" @selected($filters['status'] === 'suspended')>
                    Suspended
                </option>
            </select>

            <select name="sort" aria-label="Sort by">
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

            <select name="direction" aria-label="Sort direction">
                <option value="asc" @selected($filters['direction'] === 'asc')>
                    Ascending
                </option>
                <option value="desc" @selected($filters['direction'] === 'desc')>
                    Descending
                </option>
            </select>

            <select name="per_page" aria-label="Users per page">
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

            <button type="submit">Apply filters</button>

        </form>
    </div>


    <div class="admin-list-panel">
    <div class="dashboard-table-wrap" tabindex="0" role="region" aria-label="Users table">
    <table>

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
                        @if (auth()->id() !== $user->id && Route::has('admin.users.status'))
                            <form action="{{ route('admin.users.status', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'suspended' : 'active' }}">
                                <button type="submit">{{ $user->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No users found.</td>
                </tr>
            @endforelse
        </tbody>

    </table>
    </div>
    {{ $users->links('components.admin.pagination') }}
    </div>
@endsection
