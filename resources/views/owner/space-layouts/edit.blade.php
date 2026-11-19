<h1>Edit Space Layout</h1>

<form action="{{ route('owner.space-layouts.update', ['id' => $layout->id]) }}" method="POST">
    @csrf
    @method('PATCH')
    @include('owner.space-layouts.fields')
    <button type="submit">Save</button>
</form>
