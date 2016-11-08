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
            <th class="data-point">Active</th>
            <th>Last Seen</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
        <?php
            $dateString = '';
            if ($user->lastLoginAt && $user->lastLoginAt->timestamp > 0 && $user->center) {
                $dateString = $user->lastLoginAt->setTimezone($user->center->timezone)->format('d-M-Y');//('M j, Y @ g:ia T');
            }
        ?>
        <tr>
            <td><a href="{{ url("/admin/users/{$user->id}/edit") }}">{{ $user->firstName }} {{ $user->lastName }}</a></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role ? $user->role->display : '' }}</td>
            <td>{{ $user->center ? $user->center->name : '' }}</td>
            <td class="data-point"><span class="glyphicon {{ $user->active ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
            <td>{{ $dateString }}</td>
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
