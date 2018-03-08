@extends('template')

@section('content')

    <h2>Invite for {{ $invite->first_name }} {{ $invite->last_name[0] }} to {{ $invite->center->name }}</h2>
    <a href="{{ url('/users/invites') }}"><< See All</a><br/><br/>
    <a href="{{ url("/users/invites/{$invite->id}/edit") }}">Edit</a>

    @include('errors.results')

    <div class="table-responsive">
        <table class="table table-condensed table-striped">
            <tr>
                <th>Name:</th>
                <td>{{ $invite->firstName }} {{ $invite->lastName }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $invite->email }}</td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td>{{ $invite->phone ? $invite->formatPhone() : '-' }}</td>
            </tr>
            <tr>
                <th>Role:</th>
                <td>{{ $invite->role ? $invite->role->display : '' }}</td>
            </tr>
            <tr>
                <th>Center:</th>
                <td>{{ $invite->center ? $invite->center->name : '' }}</td>
            </tr>
            <tr>
                <th></th>
                <td>
                    {!! Form::model($invite, ['url' => "/users/invites/{$invite->id}", 'method' => 'PUT', 'class' => 'form-horizontal']) !!}
                    {!! Form::hidden('resend_invite', 1) !!}
                    {!! Form::submit('Resend Invite', ['class' => 'btn btn-default btn-primary']) !!}
                    {!! Form::close() !!}
                </td>
            </tr>
        </table>
    </div>

@endsection
