@extends('template')

@section('content')
<h2 class="sub-header">Global Reports</h2>
{{--<a href="{{ url('/globalreports/create') }}">+ Add one</a>--}}

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Reporting Date</th>
            <!-- <th>Quarter</th> -->
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
                {{-- <td>{{ $globalReport->quarter->distinction }} - {{ $globalReport->quarter->startWeekendDate->format('F Y') }}</td>--}}
                <td><i class="fa {{ $globalReport->locked ? 'fa-lock' : 'fa-unlock' }}"></i></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
