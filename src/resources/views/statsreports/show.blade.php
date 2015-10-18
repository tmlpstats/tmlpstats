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
        table {
            empty-cells: show;
        }
    </style>
@endsection

@section('content')

@if ($statsReport)
    <h2>{{ $statsReport->center->name }} - {{ $statsReport->reportingDate->format('F j, Y') }}</h2>
    <a href="{{ url('/home') }}"><< See All</a><br/><br/>

    {!! Form::open(['url' => "statsreports", 'method' => 'GET', 'class' => 'form-horizontal', 'id' => 'reportSelectorForm']) !!}
    <div class="form-group">
        {!! Form::label('report_id', 'Other Reports:', ['class' => 'col-sm-1 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('report_id', $otherStatsReports, $statsReport->id, ['class' => 'form-control reportSelector']) !!}
        </div>
    </div>
    {!! Form::close() !!}


    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#overview-tab" data-toggle="tab">Overview</a></li>
            <li><a href="#results-tab" data-toggle="tab">Validation Results</a></li>
            <li><a href="#centerstats-tab" data-toggle="tab">Center Stats</a></li>
            <li><a href="#tmlpregistrations-tab" data-toggle="tab">TMLP Registrations</a></li>
            <li><a href="#classlist-tab" data-toggle="tab">Class List</a></li>
            <li><a href="#courses-tab" data-toggle="tab">Courses</a></li>
            <li><a href="#contactinfo-tab" data-toggle="tab">Contact Info</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="overview-tab">
                @include('statsreports.details.overview', ['statsReport' => $statsReport, 'sheetUrl' => $sheetUrl])
            </div>
            <div class="tab-pane" id="centerstats-tab">
                @include('statsreports.details.centerstats', ['centerStatsData' => $centerStatsData])
            </div>
            <div class="tab-pane" id="tmlpregistrations-tab">
                @include('statsreports.details.tmlpregistrations', ['tmlpRegistrations' => $tmlpRegistrations])
            </div>
            <div class="tab-pane" id="classlist-tab">
                @include('statsreports.details.classlist', ['teamMembers' => $teamMembers])
            </div>
            <div class="tab-pane" id="courses-tab">
                @include('statsreports.details.courses', ['courses' => $courses])
            </div>
            <div class="tab-pane" id="contactinfo-tab">
                @include('statsreports.details.contactinfo', ['contacts' => $contacts])
            </div>
            <div class="tab-pane" id="results-tab">
                <h4>Results:</h4>
                <div id="results-container">
                    @include('partials.loading')
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#tabs').tab();

            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/results') }}",
                success: function(response) {
                    $("#results-container").html(response);
                }
            });

            $('select.reportSelector').change(function() {
                var baseUrl = "{{ url('statsreports') }}";
                var newReport = $('.reportSelector option:selected').val();
                window.location.replace(baseUrl + '/' + newReport);
            });
        });
    </script>

@else
    <p>Unable to find report.</p>
@endif

@endsection
