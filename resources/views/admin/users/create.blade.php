<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create user</title>
</head>
<body>
    @include('admin.partials.sidebar')
    <main>
        <h1>Create user</h1>
        <a href="{{ route('admin.users.index') }}">Back to users</a>
        @include('admin.users._form', ['user' => null, 'editing' => false])
    </main>
</body>
</html>
