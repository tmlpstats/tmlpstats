@extends('template')

@section('content')
    <h2>Edit Invite for {{ $invite->firstName }} {{ $invite->lastName }}</h2>

    @include('errors.list')

    {!! Form::model($invite, ['url' => "/users/invites/{$invite->id}", 'method' => 'PUT', 'class' => 'form-horizontal', 'autocomplete' => 'off']) !!}

    @include('admin.invites.form', ['submitButtonText' => 'Update', 'roles' => $roles])

    {!! Form::close() !!}

@stop
