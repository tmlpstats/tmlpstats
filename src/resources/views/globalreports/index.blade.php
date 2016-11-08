@extends('template')

@section('content')
<h2 class="sub-header">Global Reports</h2>

<table class="table table-hover">
    <thead>
    <tr>
        <th>Reporting Date</th>
        <th>Locked</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($globalReports as $globalReport)
        <tr>
            <td>
                <a href="{{ url('/globalreports/'.$globalReport->id) }}">
                    {{ $globalReport->reportingDate->format('F j, Y') }}
                </a>
            </td>
            <td><i class="fa {{ $globalReport->locked ? 'fa-lock' : 'fa-unlock' }}"></i></td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection
