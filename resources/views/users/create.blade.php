@extends('template')

@section('content')
<h2>Add a User</h2>

@include('errors.list')

{!! Form::open(['url' => '/admin/users', 'class' => 'form-horizontal']) !!}

    @include('users.form', ['submitButtonText' => 'Create'])

{!! Form::close() !!}

@endsection