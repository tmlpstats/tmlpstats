@extends('template')

@section('content')
<h2 class="sub-header">Centers</h2>
<a href="{{ url('/admin/centers/create') }}">+ Add one</a>
<br/><br/>

<div class="table-responsive">
    <h4>Active Centers</h4>
    <table id="activeCenterTable" class="table table-hover">
        <thead>
        <tr>
            <th>Center</th>
            <th>Team Name</th>
            <th>Global Region</th>
            <th>Local Region</th>
            <th>Email</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($centers as $center)
        @if ($center->active)
        <tr>
            <td><a href="{{ url('/admin/centers/'.$center->abbreviation) }}">{{ $center->name }}</a></td>
            <td>{{ $center->teamName }}</td>
            <td>{{ $center->globalRegion }}</td>
            <td>{{ $center->localRegion }}</td>
            <td>{{ $center->statsEmail }}</td>
            <td><span class="glyphicon {{ $center->active ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
            <td><a href="{{ url('/admin/centers/' . $center->abbreviation . '/edit') }}">Edit</a></td>
        </tr>
        @endif
        @endforeach
        </tbody>
    </table>

    <h4>Inactive Centers</h4>
    <table id="inactiveCenterTable" class="table table-hover">
        <thead>
        <tr>
            <th>Center</th>
            <th>Team Name</th>
            <th>Global Region</th>
            <th>Local Region</th>
            <th>Email</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($centers as $center)
        @if (!$center->active)
        <tr>
            <td><a href="{{ url('/admin/centers/'.$center->abbreviation) }}">{{ $center->name }}</a></td>
            <td>{{ $center->team_name }}</td>
            <td>{{ $center->global_region }}</td>
            <td>{{ $center->local_region }}</td>
            <td>{{ $center->stats_email }}</td>
            <td><span class="glyphicon {{ $center->active ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span></td>
            <td><a href="{{ url('/admin/centers/' . $center->abbreviation . '/edit') }}">Edit</a></td>
        </tr>
        @endif
        @endforeach
        </tbody>
    </table>
</div>

<script src="{{ asset('/js/query.dataTables.min.js') }}"></script>
<script src="{{ asset('/js/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#activeCenterTable').dataTable({
            "paging":    false,
            "searching": false
        });
        $('#inactiveCenterTable').dataTable({
            "paging":    false,
            "searching": false
        });
    });
</script>
@endsection