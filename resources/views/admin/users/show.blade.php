<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Details</title>
</head>

<body>

    {{-- Existing Admin Sidebar --}}
    @include('admin.partials.sidebar')

    <main class="admin-main">

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

        <header class="page-header">
            <h1>User Details</h1>

            <a href="{{ route('admin.users.index') }}">
                Back to Users
            </a>
            <a href="{{ route('admin.users.edit', $user) }}">Edit user</a>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                onsubmit="return confirm('Move this user to trash? They will be hidden from active user lists.');">
                @csrf
                @method('DELETE')
                <button type="submit">Move to trash</button>
            </form>
        </header>

        <section class="user-details">

            <h2>{{ $user->name }}</h2>

            <dl>
                <dt>User ID</dt>
                <dd>{{ $user->id }}</dd>

                <dt>Name</dt>
                <dd>{{ $user->name }}</dd>

                <dt>Email</dt>
                <dd>{{ $user->email }}</dd>

                <dt>Phone</dt>
                <dd>{{ $user->phone ?? 'Not provided' }}</dd>

                <dt>Role</dt>
                <dd>{{ $user->role }}</dd>

                <dt>Status</dt>
                <dd>{{ $user->status }}</dd>

                <dt>Registration Date</dt>
                <dd>{{ $user->created_at->format('Y-m-d H:i') }}</dd>
            </dl>

        </section>

    </main>

</body>

</html>
