@extends('template')

@section('content')

<h2>{{ $user->first_name }} {{ $user->last_name[0] }}</h2>
<a href="{{ url('/admin/users') }}"><< See All</a><br/><br/>
<a href="{{ url("/admin/users/{$user->id}/edit") }}">Edit</a>

<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <tr>
            <th>Name:</th>
            <td>{{ $user->firstName }} {{ $user->lastName }}</td>
        </tr>
        <tr>
            <th>Email:</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Phone:</th>
            <td>{{ $user->phone ? $user->formatPhone() : '-' }}</td>
        </tr>
        <tr>
            <th>Role:</th>
            <td>{{ $user->role ? $user->role->display : '' }}</td>
        </tr>
        <tr>
            <th>Center:</th>
            <td>{{ $user->center ? $user->center->name : '' }}</td>
        </tr>
        <tr>
            <th>Active:</th>
            <td><span class="glyphicon {{ $user->active ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
        </tr>
    </table>
</div>

@endsection
