@extends('layouts.admin')

@section('title', 'Edit user')
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record">
        <h1>Edit user</h1>
        <a href="{{ route('admin.users.show', $user) }}">Back to user details</a>
        @include('admin.users._form', ['user' => $user, 'editing' => true])
    </div>
@endsection
