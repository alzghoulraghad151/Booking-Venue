<h1>Edit Space Layout</h1>

<label for="name">Name</label>
<input id="name" name="name" type="text"
       value="{{ old('name', $layout->name) }}" required>

<label for="capacity">Capacity</label>
<input id="capacity" name="capacity" type="number" min="1"
       value="{{ old('capacity', $layout->capacity) }}" required>