@extends('template')

@section('content')
<h2 class="sub-header">Global Reports</h2>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Reporting Date</th>
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
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
