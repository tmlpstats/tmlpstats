@extends('template')

@section('content')
<h2>Add a Center</h2>

@include('errors.list')

{!! Form::open(['url' => '/statsreports', 'class' => 'form-horizontal']) !!}

    @include('statsreports.form', ['submitButtonText' => 'Create'])

{!! Form::close() !!}

@endsection
