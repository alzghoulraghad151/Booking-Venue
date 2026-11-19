<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit space</title>
</head>
<body>
    <main>
        <h1>Edit {{ $space->name }}</h1>
        <p>Venue: {{ $space->venue->name }}</p>
        <a href="{{ route('owner.spaces.index') }}">Back to My Spaces</a>
        @include('owner.spaces._form', ['editing' => true])
    </main>
</body>
</html>
