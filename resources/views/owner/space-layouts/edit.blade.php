@extends('layouts.owner')

@section('title', 'Edit Space Layout')

@section('content')

    <form class="admin-edit-form owner-editor" method="POST" action="{{ route('owner.space-layouts.update', ['id' => $layout->id]) }}">
        @csrf
        @method('PATCH')

        @include('owner.space-layouts.fields')

        <x-admin.edit-actions :back-url="route('owner.space-layouts.index')" back-label="Cancel" save-label="Save layout" />
    </form>
@endsection