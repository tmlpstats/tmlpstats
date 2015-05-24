{!! Form::hidden('previous_url', URL::previous()) !!}

<div class="form-group">
    {!! Form::label('first_name', 'First Name:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('first_name', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('last_name', 'Last Name:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('last_name', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('email', 'Email:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::email('email', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('phone', 'Phone:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('tel', 'phone', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('roles[]', 'Roles:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5" style="padding-left: 40px">
    @foreach ($roles as $role)
        <label class="checkbox" style="font-weight: normal">
            <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ ($user && $user->hasRole($role->name)) ? 'checked="checked"' : '' }} > {{ $role->name }}
        </label>
    @endforeach
    </div>
</div>

<div class="form-group">
    {!! Form::label('center', 'Center:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5" style="padding-left: 40px">
        {!! Form::select('center', $centers, null, ['class' => 'form-control']) !!}
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