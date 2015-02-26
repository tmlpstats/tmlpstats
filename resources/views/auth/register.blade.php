@extends('auth.template')

@section('auth.form')
{!! Form::open(['url' => '/auth/register', 'class' => 'form-horizontal']) !!}

	<div class="form-group">
		{!! Form::label('first_name', 'First Name', ['class' => 'col-md-4 control-label']) !!}
		<div class="col-md-6">
        	{!! Form::text('first_name', null, ['class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('last_name', 'Last Name', ['class' => 'col-md-4 control-label']) !!}
		<div class="col-md-6">
        	{!! Form::text('last_name', null, ['class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('phone', 'Phone', ['class' => 'col-md-4 control-label']) !!}
		<div class="col-md-6">
        	{!! Form::input('tel', 'phone', null, ['class' => 'form-control']) !!}
		</div>
	</div>

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
		{!! Form::label('password', 'Confirm Password', ['class' => 'col-md-4 control-label']) !!}
		<div class="col-md-6">
        	{!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
		</div>
	</div>
	@if (Request::has('invite'))
		{!! Form::hidden('invite_code', base64_decode(Request::get('invite'))) !!}
	@else
	<div class="form-group">
		{!! Form::label('invite_code', 'Invite Code', ['class' => 'col-md-4 control-label']) !!}
		<div class="col-md-6">
        	{!! Form::text('invite_code', null, ['class' => 'form-control']) !!}
		</div>
	</div>
	@endif

	<div class="form-group">
		<div class="col-md-6 col-md-offset-4">
        	{!! Form::submit('Register', ['class' => 'btn btn-default btn-primary']) !!}
		</div>
	</div>

{!! Form::close() !!}
@endsection
