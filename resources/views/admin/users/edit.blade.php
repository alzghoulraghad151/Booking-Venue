@extends('layouts.admin')

@section('title', 'Edit user')
@section('hide-layout-heading', '1')

@section('content')
@include('admin.partials.sidebar')
    <main>
        <h1>Edit user</h1>
        <a href="{{ route('admin.users.show', $user) }}">Back to user details</a>
        @include('admin.users._form', ['user' => $user, 'editing' => true])
    </div>
@endsection
