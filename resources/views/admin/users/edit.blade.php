@extends('layouts.admin')

@section('title', 'Edit user')
@section('hide-layout-heading', '1')

@section('content')
        <h1>Edit user</h1>
        @include('admin.users._form', ['user' => $user, 'editing' => true])
@endsection
