@extends('layouts.admin')

@section('title', 'Edit user')
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record">
        <h1>Edit user</h1>
        <p class="admin-description">Update the account’s contact details, role, or password.</p>
        <a class="admin-button admin-button--navy" href="{{ route('admin.users.show', $user) }}">Back to user details</a>
        @include('admin.users._form', ['user' => $user, 'editing' => true])
    </div>
@endsection
