@extends('template')

@section('content')
    <h2>Invitations</h2>
    <a href="{{ url('/users/invites/create') }}">+ Add one</a>
    <br/><br/>

    <div class="table-responsive">
        <table class="table table-hover want-datatable">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Center</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($invites as $invite)
                <tr>
                    <td><a href="{{ url("/users/invites/{$invite->id}") }}">{{ $invite->firstName }} {{ $invite->lastName }}</a></td>
                    <td>{{ $invite->email }}</td>
                    <td>{{ $invite->role ? $invite->role->display : '' }}</td>
                    <td>{{ $invite->center ? $invite->center->name : '' }}</td>
                    <td><a href="{{ url("/users/invites/{$invite->id}/edit") }}">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
