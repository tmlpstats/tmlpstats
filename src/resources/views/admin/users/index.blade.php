@extends('template')

@section('content')
<h2>Users</h2>
<a href="{{ url('/users/invites/create') }}">+ Invite User</a>
<br/><br/>

<div class="table-responsive">
    <table id="activeUserTable" class="table table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Center</th>
            <th>Last Seen</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($activeUsers as $user)
        <?php
            $dateString = '';
            if ($user->lastLoginAt && $user->lastLoginAt->timestamp > 0 && $user->center) {
                $dateString = $user->lastLoginAt->setTimezone($user->center->timezone)->format('d-M-Y');
            }
        ?>
        <tr>
            <td><a href="{{ url("/admin/users/{$user->id}/edit") }}">{{ $user->firstName }} {{ $user->lastName }}</a></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role ? $user->role->display : '' }}</td>
            <td>{{ $user->center ? $user->center->name : '' }}</td>
            <td>{{ $dateString }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <br/>
    <h4>Inactive Users</h4>
    <table id="inactiveUserTable" class="table table-hover want-datatable">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Center</th>
            <th>Last Seen</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($inactiveUsers as $user)
        <?php
            $dateString = '';
            if ($user->lastLoginAt && $user->lastLoginAt->timestamp > 0 && $user->center) {
                $dateString = $user->lastLoginAt->setTimezone($user->center->timezone)->format('d-M-Y');
            }
        ?>
        <tr>
            <td><a href="{{ url("/admin/users/{$user->id}/edit") }}">{{ $user->firstName }} {{ $user->lastName }}</a></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role ? $user->role->display : '' }}</td>
            <td>{{ $user->center ? $user->center->name : '' }}</td>
            <td>{{ $dateString }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#activeUserTable').dataTable({
            'paging':    false,
            'searching': false,
            'columnDefs': [
                { 'type': 'date-dd-MMM-yyyy', targets: -1 }
            ]
        })
        $('#inactiveUserTable').dataTable({
            'paging':    false,
            'searching': false,
            'columnDefs': [
                { 'type': 'date-dd-MMM-yyyy', targets: -1 }
            ]
        })
    })
</script>
@endsection
