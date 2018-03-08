@extends('template')

@section('content')
    <h2>Welcome {{ $invite->first_name }}</h2>

    @include('errors.list')

    {!! Form::model($invite, ['url' => "/invites/{$invite->token}", 'method' => 'POST', 'class' => 'form-horizontal']) !!}

    {!! Form::hidden('previous_url', URL::previous()) !!}

    <div class="form-group">
        {!! Form::label('first_name', 'First Name:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('first_name', $invite->firstName, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('last_name', 'Last Name:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('last_name', $invite->lastName, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('email', 'Email:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::email('email', $invite->email, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('phone', 'Phone:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::input('tel', 'phone', $invite->phone, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('role', 'Role:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('role', $invite->role->display, ['class' => 'form-control', 'disabled']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('center', 'Center:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('center', $invite->center->name, ['class' => 'form-control', 'disabled']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('password', 'Password', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-md-6">
            {!! Form::password('password', ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('password', 'Confirm Password', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-md-6">
            {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="btn-group col-sm-offset-2">
        {!! Form::submit('Register', ['class' => 'btn btn-default btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection




