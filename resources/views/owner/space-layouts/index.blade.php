@extends('layouts.owner')

@section('title', 'Space Layouts')
@section('hide-layout-heading', '1')

@section('content')
<h1>Space Layouts</h1>

<a href="{{ route('owner.space-layouts.create') }}">Add Layout</a>
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
            </tr>
        @empty
            <tr>
                <td colspan="3">No space layouts yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
