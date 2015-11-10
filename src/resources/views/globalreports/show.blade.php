@extends('template')

@section('content')
    <div id="content">
        <h2>{{ $region ? $region->name : 'Global' }} Report - {{ $globalReport->reportingDate->format('F j, Y') }}</h2>
        <a href="{{ \URL::previous() }}"><< Go Back</a><br/><br/>

        <div class="table-responsive" style="overflow: hidden">
            {!! Form::open(['url' => "globalreports/{$globalReport->id}", 'method' => 'GET', 'class' => 'form-horizontal', 'id' => 'reportSelectorForm']) !!}
            <div class="row">
                <div class="col-md-3">
                    {!! Form::label('region', 'Region:', ['class' => 'control-label']) !!}
                </div>
                <div class="col-md-9">
                    @if ($reportToken)
                        {!! Form::label('reportTokenUrl', 'Report Link:', ['class' => 'control-label']) !!}
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    @include('partials.forms.regions', ['selectedRegion' => $region->abbreviation, 'includeLocalRegions' => true, 'autoSubmit' => true])
                </div>
                <div class="col-md-9">
                    @if ($reportToken)
                        <input size="80" type="text" value="{{ url($reportToken->getUrl()) }}" id="reportTokenUrl" />
                    @endif
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        <br /><br />

        <div class="col-xs-2">
            <ul id="tabs " class="nav nav-tabs tabs-left" data-tabs="tabs">
                <li class="active"><a href="#ratingsummary-tab" data-toggle="tab">Ratings Summary</a></li>
                <li><a href="#regionalstats-tab" data-toggle="tab">Regional Games</a></li>
                <li><a href="#statsreports-tab" data-toggle="tab">Center Reports</a></li>
                <li><a href="#applications-tab" data-toggle="tab">Applications</a></li>
                <li><a href="#traveloverview-tab" data-toggle="tab">Travel Summary</a></li>
                <li><a href="#completedcourses-tab" data-toggle="tab">Completed Courses</a></li>
                <li><a href="#teammemberstatus-tab" data-toggle="tab">Team Member Alerts</a></li>
            </ul>
        </div>
        <div class="col-xs-10">
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

                    <div id="statsreports-container">
                        @include('partials.loading')
                    </div>
                </div>
                <div class="tab-pane" id="applications-tab">
                    <h3>Center Reports</h3>

                    <div class="btn-group" role="group">
                        <button id ="applicationsoverview-button" type="button" class="btn btn-primary">Overview</button>
                        <button id ="applicationsbystatus-button" type="button" class="btn btn-default">By Status</button>
                        <button id ="applicationsbycenter-button" type="button" class="btn btn-default">By Center</button>
                        <button id ="applicationsoverdue-button" type="button" class="btn btn-default">Overdue</button>
                    </div>
                    <div id="applicationsoverview-container">
                        @include('partials.loading')
                    </div>
                    <div id="applicationsbystatus-container" style="display: none">
                        @include('partials.loading')
                    </div>
                    <div id="applicationsoverdue-container" style="display: none">
                        @include('partials.loading')
                    </div>
                    <div id="applicationsbycenter-container" style="display: none">
                        @include('partials.loading')
                    </div>
                </div>
                <div class="tab-pane" id="traveloverview-tab">
                    <h3>Travel/Rooming Summary</h3>

                    <div id="traveloverview-container">
                        @include('partials.loading')
                    </div>
                </div>
                <div class="tab-pane" id="completedcourses-tab">
                    <h3>Course Completion Stats</h3>

                    <div id="completedcourses-container">
                        @include('partials.loading')
                    </div>
                </div>
                <div class="tab-pane" id="teammemberstatus-tab">
                    <h3>Team Members of Interest</h3>

                    <div id="teammemberstatus-container">
                        @include('partials.loading')
                    </div>
                </div>
            </div>
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

            $("#applicationsoverview-button").click(function() {
                $(this).addClass('btn-primary');
                $("#applicationsoverdue-button").addClass('btn-default');
                $("#applicationsoverdue-button").removeClass('btn-primary');
                $("#applicationsbycenter-button").addClass('btn-default');
                $("#applicationsbycenter-button").removeClass('btn-primary');
                $("#applicationsbystatus-button").addClass('btn-default');
                $("#applicationsbystatus-button").removeClass('btn-primary');

                $("#applicationsoverview-container").show();
                $("#applicationsoverdue-container").hide();
                $("#applicationsbycenter-container").hide();
                $("#applicationsbystatus-container").hide();
            });

            $("#applicationsbystatus-button").click(function() {
                $(this).addClass('btn-primary');
                $("#applicationsoverdue-button").addClass('btn-default');
                $("#applicationsoverdue-button").removeClass('btn-primary');
                $("#applicationsbycenter-button").addClass('btn-default');
                $("#applicationsbycenter-button").removeClass('btn-primary');
                $("#applicationsoverview-button").addClass('btn-default');
                $("#applicationsoverview-button").removeClass('btn-primary');

                $("#applicationsbystatus-container").show();
                $("#applicationsoverdue-container").hide();
                $("#applicationsbycenter-container").hide();
                $("#applicationsoverview-container").hide();
            });

            $("#applicationsoverdue-button").click(function() {
                $(this).addClass('btn-primary');
                $("#applicationsbystatus-button").addClass('btn-default');
                $("#applicationsbystatus-button").removeClass('btn-primary');
                $("#applicationsbycenter-button").addClass('btn-default');
                $("#applicationsbycenter-button").removeClass('btn-primary');
                $("#applicationsoverview-button").addClass('btn-default');
                $("#applicationsoverview-button").removeClass('btn-primary');

                $("#applicationsoverdue-container").show();
                $("#applicationsbystatus-container").hide();
                $("#applicationsbycenter-container").hide();
                $("#applicationsoverview-container").hide();
            });

            $("#applicationsbycenter-button").click(function() {
                $(this).addClass('btn-primary');
                $("#applicationsbystatus-button").addClass('btn-default');
                $("#applicationsbystatus-button").removeClass('btn-primary');
                $("#applicationsoverdue-button").addClass('btn-default');
                $("#applicationsoverdue-button").removeClass('btn-primary');
                $("#applicationsoverview-button").addClass('btn-default');
                $("#applicationsoverview-button").removeClass('btn-primary');

                $("#applicationsbycenter-container").show();
                $("#applicationsoverdue-container").hide();
                $("#applicationsbystatus-container").hide();
                $("#applicationsoverview-container").hide();
            });

            // Fetch Rating Summary
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/ratingsummary?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#ratingsummary-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#ratingsummary-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Regional Stats
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/regionalstats?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#regionalstats-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#regionalstats-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Stats Reports
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/statsreports?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#statsreports-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#statsreports-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Applications
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/applicationsbystatus?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#applicationsbystatus-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#applicationsbystatus-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Applications
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/applicationsbycenter?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#applicationsbycenter-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#applicationsbycenter-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Applications
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/applicationsoverdue?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#applicationsoverdue-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#applicationsoverdue-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Applications
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/applicationsoverview?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#applicationsoverview-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#applicationsoverview-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Travel
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/traveloverview?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#traveloverview-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#traveloverview-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Courses
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/completedcourses?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#completedcourses-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#completedcourses-container").html('<p>' + message + '</p>');
                }
            });
            // Fetch Team Members' Status
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/teammemberstatus?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#teammemberstatus-container").html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var message = getErrorMessage(jqXHR.status);
                    $("#teammemberstatus-container").html('<p>' + message + '</p>');
                }
            });
        });
    </script>
@endsection
