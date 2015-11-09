@extends('template')

@section('content')

@if ($statsReport)
    <h2>{{ $statsReport->center->name }} - {{ $statsReport->reportingDate->format('F j, Y') }}</h2>
    @can ('index', TmlpStats\StatsReport::class)
    <a href="{{ url('/home') }}"><< See All</a><br/><br/>
    @endcan

    <div class="table-responsive" style="overflow: hidden">
        {!! Form::open(['url' => "statsreports", 'method' => 'GET', 'class' => 'form-horizontal', 'id' => 'reportSelectorForm']) !!}
        <div class="row">
            <div class="col-md-3" style="align-content: center">
                {!! Form::label('report_id', 'Other Weekly Reports:', ['class' => 'control-label']) !!}
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-7">
                @if ($reportToken)
                {!! Form::label('reportTokenUrl', 'Report Link:', ['class' => 'control-label']) !!}
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-3" style="align-content: center">
                {!! Form::select('report_id', $otherStatsReports, $statsReport->id, ['class' => 'form-control reportSelector']) !!}
            </div>
            <div class="col-md-2">
                @if ($globalReport)
                    <a class="btn btn-primary" href="{{ url("globalreports/{$globalReport->id}?region={$statsReport->center->region->abbreviation}") }}">View Regional Report</a>
                @endif
            </div>
            <div class="col-md-7">
                @if ($reportToken)
                    {!! Form::text('reportTokenUrl', url($reportToken->getUrl()), ['size' => 80]) !!}
                @endif
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <br />
    <br />

    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#summary-tab" data-toggle="tab">Weekly Summary</a></li>
            <li><a href="#overview-tab" data-toggle="tab">Report Details</a></li>
            <li><a href="#centerstats-tab" data-toggle="tab">Center Games</a></li>
            <li><a href="#classlist-tab" data-toggle="tab">Team Members</a></li>
            <li><a href="#tmlpregistrations-tab" data-toggle="tab">Team Expansion</a></li>
            <li><a href="#courses-tab" data-toggle="tab">Courses</a></li>
            @can ('readContactInfo', $statsReport)
            <li><a href="#contactinfo-tab" data-toggle="tab">Contact Info</a></li>
            @endcan
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="summary-tab">
                <h3>Week Summary</h3>
                <div id="summary-container">
                    @include('partials.loading')
                </div>
            </div>
            <div class="tab-pane" id="overview-tab">
                <h3>Report Details</h3>
                @include('statsreports.details.overview', ['statsReport' => $statsReport, 'sheetUrl' => $sheetUrl])
                <h3>Results</h3>
                <div id="results-container">
                    @include('partials.loading')
                </div>
            </div>
            <div class="tab-pane" id="centerstats-tab">
                <h3>Center Games</h3>
                <div id="centerstats-container">
                    @include('partials.loading')
                </div>
            </div>
            <div class="tab-pane" id="classlist-tab">
                <h3>Team Members</h3>
                <div class="btn-group" role="group">
                    <button id ="classlist-button" type="button" class="btn btn-primary">Summary</button>
                    <button id ="gitwsummary-button" type="button" class="btn btn-default">GITW</button>
                    <button id ="tdosummary-button" type="button" class="btn btn-default">TDO</button>
                </div>
                <div id="classlist-container">
                    @include('partials.loading')
                </div>
                <div id="gitwsummary-container" style="display: none">
                    @include('partials.loading')
                </div>
                <div id="tdosummary-container" style="display: none">
                    @include('partials.loading')
                </div>
            </div>
            <div class="tab-pane" id="tmlpregistrations-tab">
                <h3>Team Expansion</h3>
                <div class="btn-group" role="group">
                    <button id ="tmlpregistrations-button" type="button" class="btn btn-primary">By Team Year</button>
                    <button id ="tmlpregistrationsbystatus-button" type="button" class="btn btn-default">By Status</button>
                </div>
                <div id="tmlpregistrations-container">
                    @include('partials.loading')
                </div>
                <div id="tmlpregistrationsbystatus-container" style="display: none">
                    @include('partials.loading')
                </div>
            </div>
            <div class="tab-pane" id="courses-tab">
                <h3>Courses</h3>
                <div id="courses-container">
                    @include('partials.loading')
                </div>
            </div>
            @can ('readContactInfo', $statsReport)
            <div class="tab-pane" id="contactinfo-tab">
                <h3>Contact Info</h3>
                <div id="contactinfo-container">
                    @include('partials.loading')
                </div>
            </div>
            @endcan
        </div>
    </div>

    <script type="text/javascript">

        function getErrorMessage(code) {
            var message = '';
            if (code == 404) {
                message = 'Unable to find report.';
            } else if (code == 403) {
                message = 'You do not have access to this report.';
            } else {
                message = 'Unable to get report.';
            }
            return message;
        }

        jQuery(document).ready(function ($) {
            $('#tabs').tab();

            $('select.reportSelector').change(function() {
                var baseUrl = "{{ url('statsreports') }}";
                var newReport = $('.reportSelector option:selected').val();
                window.location.replace(baseUrl + '/' + newReport);
            });

            $("#tmlpregistrations-button").click(function() {
                $(this).addClass('btn-primary');
                $("#tmlpregistrationsbystatus-button").addClass('btn-default');
                $("#tmlpregistrationsbystatus-button").removeClass('btn-primary');

                $("#tmlpregistrations-container").show();
                $("#tmlpregistrationsbystatus-container").hide();
            });

            $("#tmlpregistrationsbystatus-button").click(function() {
                $(this).addClass('btn-primary');
                $("#tmlpregistrations-button").addClass('btn-default');
                $("#tmlpregistrations-button").removeClass('btn-primary');

                $("#tmlpregistrationsbystatus-container").show();
                $("#tmlpregistrations-container").hide();
            });



            $("#classlist-button").click(function() {
                $(this).addClass('btn-primary');
                $("#gitwsummary-button").addClass('btn-default');
                $("#gitwsummary-button").removeClass('btn-primary');
                $("#tdosummary-button").addClass('btn-default');
                $("#tdosummary-button").removeClass('btn-primary');

                $("#classlist-container").show();
                $("#gitwsummary-container").hide();
                $("#tdosummary-container").hide();
            });

            $("#gitwsummary-button").click(function() {
                $(this).addClass('btn-primary');
                $("#classlist-button").addClass('btn-default');
                $("#classlist-button").removeClass('btn-primary');
                $("#tdosummary-button").addClass('btn-default');
                $("#tdosummary-button").removeClass('btn-primary');

                $("#gitwsummary-container").show();
                $("#classlist-container").hide();
                $("#tdosummary-container").hide();
            });

            $("#tdosummary-button").click(function() {
                $(this).addClass('btn-primary');
                $("#classlist-button").addClass('btn-default');
                $("#classlist-button").removeClass('btn-primary');
                $("#gitwsummary-button").addClass('btn-default');
                $("#gitwsummary-button").removeClass('btn-primary');

                $("#tdosummary-container").show();
                $("#gitwsummary-container").hide();
                $("#classlist-container").hide();
            });



            // Fetch Summary
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/summary') }}",
                success: function(response) {
                    $("#summary-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#summary-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch Validation Results
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/results') }}",
                success: function(response) {
                    $("#results-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#results-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch Center Stats data
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/centerstats') }}",
                success: function(response) {
                    $("#centerstats-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#centerstats-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch Classlist
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/classlist') }}",
                success: function(response) {
                    $("#classlist-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#classlist-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch GITW Summary
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/gitwsummary') }}",
                success: function(response) {
                    $("#gitwsummary-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#gitwsummary-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch TDO Summary
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/tdosummary') }}",
                success: function(response) {
                    $("#tdosummary-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#tdosummary-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch Team Registrations
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/tmlpregistrations') }}",
                success: function(response) {
                    $("#tmlpregistrations-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#tmlpregistrations-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch Team Registrations By Status
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/tmlpregistrationsbystatus') }}",
                success: function(response) {
                    $("#tmlpregistrationsbystatus-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#tmlpregistrationsbystatus-container").html('<p>' + message + '</p>');
                }
            });

            // Fetch Courses
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/courses') }}",
                success: function(response) {
                    $("#courses-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#courses-container").html('<p>' + message + '</p>');
                }
            });

            @can ('readContactInfo', $statsReport)
            // Fetch Contact Info
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/contactinfo') }}",
                success: function(response) {
                    $("#contactinfo-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#contactinfo-container").html('<p>' + message + '</p>');
                }
            });
            @endcan
        });
    </script>

@else
    <p>Unable to find report.</p>
@endif

@endsection
