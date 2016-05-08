@extends('template')

@section('content')
<h2>Add a User</h2>

@include('errors.list')

{!! Form::open(['url' => '/admin/users', 'class' => 'form-horizontal', 'autocomplete' => 'off']) !!}

    @include('users.form', ['submitButtonText' => 'Create', 'user' => null, 'roles' => $roles])

{!! Form::close() !!}

@endsection
