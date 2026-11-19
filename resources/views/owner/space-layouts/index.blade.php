<h1>Space Layouts</h1>

 <a href="{{ route('owner.space-layouts.crete') }}"></a>
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
                </a></td>
                   Edit
                </a>
            </tr>
        @empty
            <tr>
                <td colspan="4">No space layouts yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>