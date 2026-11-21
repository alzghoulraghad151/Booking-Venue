@extends('layouts.admin')

@section('title', 'Create user')
@section('hide-layout-heading', '1')

@section('content')
        <h1>Create user</h1>
        @include('admin.users._form', ['user' => null, 'editing' => false])
@endsection
