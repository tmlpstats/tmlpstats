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

        var pages = [
            'ratingsummary',
            'regionalstats',
            'statsreports',
            'applicationsbystatus',
            'applicationsbycenter',
            'applicationsoverdue',
            'applicationsoverview',
            'traveloverview',
            'completedcourses',
            'teammemberstatus',
        ];

        var buttonGroups = [
            [
                'applicationsoverview',
                'applicationsoverdue',
                'applicationsbycenter',
                'applicationsbystatus',
            ],
        ];

        $(document).ready(function ($) {
            $('#tabs').tab();

            // Load all of the pages
            $.each(pages, function (index, page) {
                var url = "{{ url("/globalreports/{$globalReport->id}") }}/" + page + "?region={{$region->abbreviation}}";
                var container = "#" + page + "-container";
                $.get(url, function (response) {
                    $(container).html(response);
                }).fail(function (jqXHR) {
                    var message = getErrorMessage(jqXHR.status);
                    $(container).html('<p>' + message + '</p>');
                });
            });

            // Setup the button click events
            $.each(buttonGroups, function (i, buttons) {
                $.each(buttons, function (j, primaryName) {
                    var primaryButton = "#" + primaryName + "-button";
                    var primaryContainer = "#" + primaryName + "-container";

                    $(primaryButton).click(function () {
                        $.each(buttons, function (k, secondaryName) {
                            if (primaryName == secondaryName) {
                                $(primaryButton).addClass('btn-primary');
                                $(primaryButton).removeClass('btn-default');
                                $(primaryContainer).show();
                            } else {
                                var secondaryButton = "#" + secondaryName + "-button";
                                var secondaryContainer = "#" + secondaryName + "-container";
                                $(secondaryButton).addClass('btn-default');
                                $(secondaryButton).removeClass('btn-primary');
                                $(secondaryContainer).hide();
                            }
                        });
                    });
                });
            });
        });
    </script>
@endsection
