@extends('template')

@section('content')
<h2>Quarters</h2>
<a href="{{ url('/admin/quarters/create') }}">+ Add one</a>
<br/><br/>

<div class="table-responsive">
    <table id="quartersTable" class="table table-hover">
        <thead>
        <tr>
            <th>Region</th>
            <th>Location</th>
            <th>Distinction</th>
            <th>Start Date</th>
            <th>Classroom 1</th>
            <th>Classroom 2</th>
            <th>Classroom 3</th>
            <th>Completion Date</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($quarters as $quarter)
        <tr>
            <td>{{ $quarter->global_region }}</td>
            <td><a href="{{ url('/admin/quarters/'.$quarter->id) }}">{{ $quarter->location }}</a></td>
            <td>{{ $quarter->distinction }}</td>
            <td>{{ ($quarter->start_weekend_date->format('Y') < 0) ? '-' : $quarter->start_weekend_date->format('M d, Y') }}</td>
            <td>{{ ($quarter->classroom1_date->format('Y') < 0) ? '-' : $quarter->classroom1_date->format('M d, Y') }}</td>
            <td>{{ ($quarter->classroom2_date->format('Y') < 0) ? '-' : $quarter->classroom2_date->format('M d, Y') }}</td>
            <td>{{ ($quarter->classroom3_date->format('Y') < 0) ? '-' : $quarter->classroom3_date->format('M d, Y') }}</td>
            <td>{{ ($quarter->end_weekend_date->format('Y') < 0) ? '-' : $quarter->end_weekend_date->format('M d, Y') }}</td>
            <td><a href="{{ url('/admin/quarters/' . $quarter->id . '/edit') }}">Edit</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<script src="{{ asset('/js/query.dataTables.min.js') }}"></script>
<script src="{{ asset('/js/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#quartersTable').dataTable({
            "paging":    false,
            "searching": false
        });
    });
</script>
@endsection
