{!! Form::hidden('previous_url', URL::previous()) !!}

<div class="form-group">
    {!! Form::label('first_name', 'First Name:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('first_name', $user ? $user->firstName : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('last_name', 'Last Name:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('last_name', $user ? $user->lastName : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('email', 'Email:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::email('email', $user ? $user->email : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('phone', 'Phone:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('tel', 'phone', $user ? $user->phone : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('role', 'Role:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::select('role', $roles, $user ? $user->roleId : $selectedRole ?: null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('center', 'Center:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::select('center', $centers, ($user && $user->center) ? $user->center->abbreviation : null, ['class' => 'form-control']) !!}
    </div>
</div>

@if ($submitButtonText == 'Update')
<div class="form-group">
    {!! Form::label('require_password_reset', 'Require Password Reset:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::checkbox('require_password_reset', 1, false, ['class' => 'form-control']) !!}
    </div>
</div>
@endif

<div class="btn-group col-sm-offset-2">
        {!! link_to($submitButtonText == 'Create' ? url('admin/users') : URL::previous(), 'Cancel', ['class' => 'btn btn-default']) !!}
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-default btn-primary']) !!}
</div>
