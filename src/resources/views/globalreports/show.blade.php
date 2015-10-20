@extends('template')

@section('content')

<h2>Global Report - {{ $globalReport->reportingDate->format('F j, Y') }}</h2>
<a href="{{ url('/globalreports') }}"><< See All</a><br/><br/>

<div id="content">
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li><a href="#overview-tab" data-toggle="tab">Overview</a></li>
        <li class="active"><a href="#ratingsummary-tab" data-toggle="tab">Ratings Summary</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane" id="overview-tab">
            @include('globalreports.details.overview', ['globalReport' => $globalReport, 'centers' => $centers])
        </div>
        <div class="tab-pane active" id="ratingsummary-tab">
            <h3>Ratings Summary</h3>
            <div id="ratingsummary-container">
                @include('globalreports.details.ratingsummary', compact('ratings', 'points', 'centerReports'))
            </div>
        </div>
    </div>
</div>
@endsection
