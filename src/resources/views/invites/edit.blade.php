@extends('template')

@section('content')
    <h2>Edit Invite for {{ $invite->first_name }} {{ $invite->last_name[0] }}</h2>

    @include('errors.list')

    {!! Form::model($invite, ['url' => "/users/invites/{$invite->id}", 'method' => 'PUT', 'class' => 'form-horizontal']) !!}

    @include('invites.form', ['submitButtonText' => 'Update', 'roles' => $roles])

    {!! Form::close() !!}

@stop
