<h1>Edit Space Layout</h1>
       
<form method="POST" action="{{ route('owner.space-layouts.update', ['id' => $layout->id]) }}">
    @csrf
    @method('PATCH')

   <div>
        <label for="name">Name</label>
        <input type="text" id="name" name="name"
               value="{{ old('name', $layout->name) }}" maxlength="255" required>
    </div>

    <div>
        <label for="capacity">Capacity</label>
        <input type="number" id="capacity" name="capacity"
               value="{{ old('capacity', $layout->capacity) }}" min="1" required>
    </div>


    <button type="submit">Save</button>
</form>