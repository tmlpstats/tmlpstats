@extends('template')

@section('content')
<h2>Edit {{ $user->first_name }} {{ $user->last_name[0] }}</h2>


@include('errors.list')

{!! Form::model($user, ['url' => "/admin/users/{$user->id}", 'method' => 'PUT', 'class' => 'form-horizontal']) !!}

    @include('users.form', ['submitButtonText' => 'Update', 'roles' => $roles])

{!! Form::close() !!}

@stop
