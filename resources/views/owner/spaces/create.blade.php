@extends('layouts.owner')

@section('title', 'Add space')
@section('hide-layout-heading', '1')

@section('content')
<div>
        <h1>Add a space</h1>
        @if ($venues->isEmpty())
            <p>You need an existing venue before adding a space.</p>
        @else
            @include('owner.spaces._form', ['space' => null, 'editing' => false])
        @endif
    </div>
@endsection
