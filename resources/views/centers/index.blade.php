@extends('template')

@section('content')
<h2 class="sub-header">Centers</h2>
<a href="{{ url('/admin/centers/create') }}">+ Add one</a>
<br/><br/>

<div class="table-responsive">
    <table class="table table-hover">
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
        <tr>
            <td><a href="{{ url('/admin/centers/'.$center->abbreviation) }}">{{ $center->name }}</a></td>
            <td>{{ $center->team_name }}</td>
            <td>{{ $center->global_region }}</td>
            <td>{{ $center->local_region }}</td>
            <td>{{ $center->stats_email }}</td>
            <td>{{ $center->active == true ? 'yes' : 'no' }}</td>
            <td><a href="{{ url('/admin/centers/' . $center->abbreviation . '/edit') }}">Edit</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection