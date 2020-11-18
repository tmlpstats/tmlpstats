{!! Form::hidden('previous_url', URL::previous()) !!}

<div class="form-group">
    {!! Form::label('first_name', 'First Name:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('first_name', $invite ? $invite->firstName : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('last_name', 'Last Name Initial:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('last_name', $invite ? $invite->lastName : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('email', 'Email:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::email('email', $invite ? $invite->email : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('phone', 'Phone:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('tel', 'phone', $invite ? $invite->phone : null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('role', 'Role:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::select('role', $roles, $invite ? $invite->roleId : $selectedRole ?: null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('center', 'Center:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::select('center', $centers, ($invite && $invite->center) ? $invite->center->abbreviation : null, ['class' => 'form-control']) !!}
    </div>
</div>

@if ($submitButtonText == 'Update')
    <div class="form-group">
        {!! Form::label('center', 'Invited by:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5" style="padding-top: 7px">
            {{ $invite->invitedByUser->firstName }} {{ $invite->invitedByUser->lastName }}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('center', 'Created At:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5" style="padding-top: 7px">
            {{ Auth::user()->toLocalTimezone($invite->createdAt)->format('M j, Y \a\t g:i a T') }}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('center', 'Last Invite Sent At:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5" style="padding-top: 7px">
            @if ($invite->emailSentAt)
            {{ Auth::user()->toLocalTimezone($invite->emailSentAt)->format('M j, Y \a\t g:i a T') }}
            @else
            Not Sent
            @endif
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('resend_invite', 'Resend Invite:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::checkbox('resend_invite', 1, false, ['class' => 'form-control']) !!}
        </div>
    </div>
@endif

<div class="btn-group col-sm-offset-2">
    {!! link_to(url('users/invites'), 'Cancel', ['class' => 'btn btn-default']) !!}
    {!! Form::submit($submitButtonText, ['class' => 'btn btn-default btn-primary']) !!}
</div>
