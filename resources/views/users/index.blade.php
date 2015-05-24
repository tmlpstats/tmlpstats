@extends('template')

@section('content')
<h2>Users</h2>
<a href="{{ url('/admin/users/create') }}">+ Add one</a>
<br/><br/>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Roles</th>
            <th>Center</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
        <tr>
            <td><a href="{{ url('/admin/users/'.$user->id) }}">{{ $user->first_name }} {{ $user->last_name }}</a></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone ? $user->formatPhone() : '-' }}</td>
            <td>{{ implode(', ', array_fetch($user->roles->toArray(), 'name')) }}</td>
            <td>{{ implode(', ', array_fetch($user->centers->toArray(), 'name')) }}</td>
            <td><span class="glyphicon {{ $user->active ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
            <td><a href="{{ url('/admin/users/' . $user->id . '/edit') }}">Edit</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
