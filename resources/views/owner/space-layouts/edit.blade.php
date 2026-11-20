@extends('layouts.owner')

@section('title', 'Edit Space Layout')

@section('content')

    <form method="POST" action="{{ route('owner.space-layouts.update', ['id' => $layout->id]) }}">
        @csrf
        @method('PATCH')

        @include('owner.space-layouts.fields')

        <button type="submit">Save Layout</button>
        <a href="{{ route('owner.space-layouts.index') }}">Cancel</a>
    </form>
@endsection