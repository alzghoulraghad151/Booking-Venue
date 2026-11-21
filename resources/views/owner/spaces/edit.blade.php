@extends('layouts.owner')

@section('title', 'Edit space')
@section('hide-layout-heading', '1')

@section('content')
<div>
        <h1>Edit {{ $space->name }}</h1>
        <p>Venue: {{ $space->venue->name }}</p>
        @include('owner.spaces._form', ['editing' => true])
    </div>
@endsection
