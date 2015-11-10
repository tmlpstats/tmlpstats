@extends('template')

@section('content')
    <h2>Regions</h2>
    {{--<a href="{{ url('/regions/create') }}">+ Add one</a>--}}
    <br/><br/>

    <div class="table-responsive">
        <table id="mainTable" class="table table-hover">
            <thead>
            <tr>
                <th>Name</th>
                <th>Abbreviation</th>
                <th>Regional Email</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($regions as $region)
                <tr>
                    <td><a href="{{ url("/regions/{$region->id}") }}">{{ $region->name }}</a></td>
                    <td>{{ $region->abbreviation }}</td>
                    <td>{{ $region->email ? $region->email : '' }}</td>
                    <td>{{ $region->center ? $region->center->name : '' }}</td>
                    <td><a href="{{ url("/regions/{$region->id}/edit") }}">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#mainTable').dataTable({
                "paging":    false,
                "searching": false
            });
        });
    </script>
@endsection
