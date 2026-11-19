<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add space</title>
</head>
<body>
    <main>
        <h1>Add a space</h1>
        <a href="{{ route('owner.spaces.index') }}">Back to My Spaces</a>
        @if ($venues->isEmpty())
            <p>You need an existing venue before adding a space.</p>
        @else
            @include('owner.spaces._form', ['space' => null, 'editing' => false])
        @endif
    </main>
</body>
</html>
