<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit user</title>
</head>
<body>
    @include('admin.partials.sidebar')
    <main>
        <h1>Edit user</h1>
        <a href="{{ route('admin.users.show', $user) }}">Back to user details</a>
        @include('admin.users._form', ['user' => $user, 'editing' => true])
    </main>
</body>
</html>
