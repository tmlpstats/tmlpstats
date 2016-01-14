@extends('template')

@section('content')
<h2>Users</h2>
<a href="{{ url('/admin/users/create') }}">+ Add one</a>
<br/>
<a href="{{ url('/users/invites/create') }}">+ Invite User</a>
<br/><br/>

<div class="table-responsive">
    <table id="mainTable" class="table table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Center</th>
            <th>Active</th>
            <th>Last Seen</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
        <tr>
            <td><a href="{{ url("/admin/users/{$user->id}") }}">{{ $user->firstName }} {{ $user->lastName }}</a></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role ? $user->role->display : '' }}</td>
            <td>{{ $user->center ? $user->center->name : '' }}</td>
            <td><span class="glyphicon {{ $user->active ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
            <td><?php
                $dateString = '';
                if ($user->lastLoginAt && $user->lastLoginAt->timestamp > 0 && $user->center) {
                    $dateString = $user->lastLoginAt->setTimezone($user->center->timezone)->format('d-M-Y');//('M j, Y @ g:ia T');
                }
                echo $dateString;
            ?></td>
            <td><a href="{{ url("/admin/users/{$user->id}/edit") }}">Edit</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#mainTable').dataTable({
            "paging":    false,
            "searching": false,
            "columnDefs": [
                { "type": "date-dd-MMM-yyyy", targets: -1 }
            ]
        });
    });
</script>
@endsection
