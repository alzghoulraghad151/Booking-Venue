<h1>Space Layouts</h1>
<a href="{{ route('owner.space-layouts.create') }}">Add Space Layout</a>
<table>
    <thead>
        <tr>
            <th>Layout Name</th>
            <th>Space</th>
            <th>Capacity</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($layouts as $layout)
            <tr>
                <td>{{ $layout->name }}</td>
                <td>{{ $layout->space->name }}</td>
                <td>{{ $layout->capacity }}</td>
                <td><a href="{{ route('owner.space-layouts.edit', ['id' => $layout->id]) }}"> Edit
                </a>
                   <form method="POST" action="{{ route('owner.space-layouts.destroy', ['id' => $layout->id]) }}">
                     @csrf
                     @method('DELETE')
                 
                     <button type="submit">Delete</button>
                    </form>
                </td>
                  
                
            </tr>
        @empty
            <tr>
                <td colspan="4">No space layouts yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>