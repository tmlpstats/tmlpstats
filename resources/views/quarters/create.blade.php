@extends('template')

@section('content')
<h2>Add a Quarter</h2>

@include('errors.list')

{!! Form::open(['url' => '/admin/quarters', 'class' => 'form-horizontal']) !!}

    @include('quarters.form', ['submitButtonText' => 'Create'])

{!! Form::close() !!}

@endsection