@extends('template')

@section('content')
    <h1>
        Team {{ $center->name }} <span class="date-title">- {{ $reportingDate->format('F j, Y') }}</span>
        @if ($alreadySubmitted)
            <span class="already-submitted">(submitted)</span>
        @endif
    </h1>
    <div id="react-routed-flow"></div>
@endsection
