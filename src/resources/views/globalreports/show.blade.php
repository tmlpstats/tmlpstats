@extends('template')

@section('headers')
    <style type="text/css">
        table.centerStatsTable {
            width: 95%;
        }
        .centerStatsTable th, .centerStatsTable td {
            text-align: center;
        }
        .centerStatsTable td {
            width: 4em;
        }

        .centerStatsSummaryTable th, .centerStatsSummaryTable td {
            text-align: center;
            width: 4em;
        }
        table.centerStatsSummaryTable {
            width: 400px;
        }
        table {
            empty-cells: show;
        }

        .table-hover>tbody>tr:hover>td, .table-hover>tbody>tr:hover>th {
            background-color: #DDDDDD;
        }

        .ratingsTable .points td {
            padding: 0px !important;
            margin: 0px !important;
            height: 2em;
            vertical-align: middle;
        }

        .meter > span {
            display: block;
            height: 100%;
            background-color: #0075b0;
            color: white;
            position: relative;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')

<h2>Global Report - {{ $globalReport->reportingDate->format('F j, Y') }}</h2>
<a href="{{ \URL::previous() }}"><< See All</a><br/><br/>

<div id="content">
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active"><a href="#ratingsummary-tab" data-toggle="tab">Ratings Summary</a></li>
        <li><a href="#regionalstats-tab" data-toggle="tab">Regional Games</a></li>
        <li><a href="#statsreports-tab" data-toggle="tab">Center Reports</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="ratingsummary-tab">
            <h3>Ratings Summary</h3>
            <div id="ratingsummary-container">
                @include('partials.loading')
            </div>
        </div>
        <div class="tab-pane" id="regionalstats-tab">
            <h3>Regional Games</h3>
            <div id="regionalstats-container">
                @include('partials.loading')
            </div>
        </div>
        <div class="tab-pane" id="statsreports-tab">
            <h3>Center Reports</h3>
            @include('globalreports.details.statsreports', ['globalReport' => $globalReport, 'centers' => $centers])
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#tabs').tab();

        // Fetch Rating Summary
        $.ajax({
            type: "GET",
            url: "{{ url('/globalreports/' . $globalReport->id . '/ratingsummary') }}",
            success: function(response) {
                $("#ratingsummary-container").html(response);
            }
        });
        // Fetch Regional Stats
        $.ajax({
            type: "GET",
            url: "{{ url('/globalreports/' . $globalReport->id . '/regionalstats') }}",
            success: function(response) {
                $("#regionalstats-container").html(response);
            }
        });
    });
</script>
@endsection
