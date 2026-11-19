<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Venue</title>
</head>
<body>
    <main>
        <h1>Add Venue</h1>



        <form method="POST" action="{{ route('owner.venues.store') }}">
    @csrf

    <div>
        <label for="name">Venue name</label>
        <input id="name" name="name" type="text"
               value="{{ old('name') }}" maxlength="255" required>
        @error('name')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="address">Address</label>
        <input id="address" name="address" type="text"
               value="{{ old('address') }}" maxlength="255" required>
        @error('address')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description">Description</label>
        <textarea id="description" name="description"
                  maxlength="5000">{{ old('description') }}</textarea>
        @error('description')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">Save Venue</button>
</form>

       
        <a href="{{ route('owner.venues.index') }}">Back to My Venues</a>
    </main>
</body>
</html>