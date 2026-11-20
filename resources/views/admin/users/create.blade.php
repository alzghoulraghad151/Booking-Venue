@extends('layouts.admin')

@section('title', 'Create user')
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record">
        <h1>Create user</h1>
        <p class="admin-description">Set up a new account with contact details and a platform role.</p>
        <a class="admin-button admin-button--navy" href="{{ route('admin.users.index') }}">Back to users</a>
        @include('admin.users._form', ['user' => null, 'editing' => false])
    </div>
@endsection
