@extends('layouts.admin')

@section('title', 'Edit user')
@section('hide-layout-heading', '1')

@section('content')
    <div class="admin-record">
        <h1>Edit user</h1>
        @include('admin.users._form', ['user' => $user, 'editing' => true])
    </div>
@endsection
