@extends('layouts.owner')

@section('title', 'Edit Space Layout')

@section('content')
    <h1>Edit Space Layout</h1>

    <form action="{{ route('owner.space-layouts.update', $layout->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div>
            <label for="name">Layout Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $layout->name) }}" maxlength="100" required>
            @error('name')<p role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="capacity">Capacity</label>
            <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $layout->capacity) }}" required>
            @error('capacity')<p role="alert">{{ $message }}</p>@enderror
        </div>

        <button type="submit">Save Layout</button>
        <a href="{{ route('owner.space-layouts.index') }}">Cancel</a>
    </form>
@endsection
