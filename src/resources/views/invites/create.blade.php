@extends('template')

@section('content')
    <h2>Create an Invitation</h2>

    @include('errors.list')

    {!! Form::open(['url' => '/users/invites', 'class' => 'form-horizontal']) !!}

    @include('invites.form', ['submitButtonText' => 'Create', 'invite' => null, 'roles' => $roles])

    {!! Form::close() !!}

@endsection
