@extends('layouts.owner')

@section('title', 'Add Space Layout')
@section('hide-layout-heading', '1')

@section('content')
<h1>Add Space Layout</h1>

<form class="admin-edit-form owner-editor" action="{{ route('owner.space-layouts.store') }}" method="POST">
    @csrf
    @include('owner.space-layouts.fields')
    <x-admin.edit-actions :back-url="route('owner.space-layouts.index')" save-label="Add space layout" />
</form>
@endsection
