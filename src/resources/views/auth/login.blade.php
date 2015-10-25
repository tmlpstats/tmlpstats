@extends('auth.template')

@section('auth.form')

@if (isset($message))
    <div class="alert alert-danger" role="alert">
        <p>{{ $message }}</p>
    </div>
@endif

{!! Form::open(['url' => '/auth/login', 'class' => 'form-horizontal']) !!}

    <div class="form-group">
        {!! Form::label('email', 'E-Mail', ['class' => 'col-md-4 control-label']) !!}
        <div class="col-md-6">
            {!! Form::email('email', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('password', 'Password', ['class' => 'col-md-4 control-label']) !!}
        <div class="col-md-6">
            {!! Form::password('password', ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('remember') !!} Remember Me
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            {!! Form::submit('Login', ['class' => 'btn btn-default btn-success']) !!}

            <a href="{!! url('/password/email') !!}">Forgot Your Password?</a>
        </div>
    </div>

{!! Form::close() !!}
@endsection
