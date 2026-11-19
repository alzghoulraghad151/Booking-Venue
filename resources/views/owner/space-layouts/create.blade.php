<h1>Add Space Layout</h1>

<form action="{{ route('owner.space-layouts.store') }}" method="POST">
    @csrf

    <div>
        <label for="space_id">Space</label>
        <select id="space_id" name="space_id" required>
            <option value="">Select a space</option>

            @foreach ($spaces as $space)
                <option value="{{ $space->id }}">{{ $space->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="name">Layout Name</label>
        <input id="name" name="name" type="text" required>
    </div>

    <div>
        <label for="capacity">Capacity</label>
        <input id="capacity" name="capacity" type="number" min="1" required>
    </div>

    <button type="submit">Save Layout</button>
</form>