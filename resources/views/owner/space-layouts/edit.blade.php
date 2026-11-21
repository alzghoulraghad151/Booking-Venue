@extends('layouts.owner')

@section('title', 'Edit Space Layout')

@section('content')

    <form class="admin-edit-form owner-editor" method="POST" action="{{ route('owner.space-layouts.update', ['id' => $layout->id]) }}">
        @csrf
        @method('PATCH')

        @include('owner.space-layouts.fields')

        <button class="admin-button admin-button--primary admin-button--page" type="submit">Save Layout</button>
        <a href="{{ route('owner.space-layouts.index') }}">Cancel</a>
    </form>
@endsection