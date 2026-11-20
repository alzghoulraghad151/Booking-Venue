@extends('layouts.admin')

@section('title', 'Create user')
@section('hide-layout-heading', '1')

@section('content')
@include('admin.partials.sidebar')
    <main>
        <h1>Create user</h1>
        <a href="{{ route('admin.users.index') }}">Back to users</a>
        @include('admin.users._form', ['user' => null, 'editing' => false])
    </div>
@endsection
