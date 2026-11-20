@extends('layouts.owner')

@section('title', 'Add Space Layout')
@section('hide-layout-heading', '1')

@section('content')
<h1>Add Space Layout</h1>

<form action="{{ route('owner.space-layouts.store') }}" method="POST">
    @csrf
    @include('owner.space-layouts.fields')
    <button type="submit">Save Layout</button>
</form>
@endsection
