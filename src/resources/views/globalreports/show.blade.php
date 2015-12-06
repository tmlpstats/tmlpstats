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
                <li><a href="#courses-tab" data-toggle="tab">Courses</a></li>
                <li><a href="#teammemberstatus-tab" data-toggle="tab">Team Members</a></li>
            </ul>
        </div>
        <div class="col-xs-10">
            <div class="tab-content">
                <div class="tab-pane active" id="ratingsummary-tab">
                    <h3>Ratings Summary</h3>

                    <div id="ratingsummary-container"></div>
                </div>
                <div class="tab-pane" id="regionalstats-tab">
                    <h3>Regional Games</h3>

                    <div id="regionalstats-container"></div>
                </div>
                <div class="tab-pane" id="statsreports-tab">
                    <h3>Center Reports</h3>

                    <div id="statsreports-container"></div>
                </div>
                <div class="tab-pane" id="applications-tab">
                    <h3>Center Reports</h3>

                    <div class="btn-group" role="group">
                        <button id ="applicationsoverview-button" type="button" class="btn">Overview</button>
                        <button id ="applicationsbystatus-button" type="button" class="btn">By Status</button>
                        <button id ="applicationsbycenter-button" type="button" class="btn">By Center</button>
                        <button id ="applicationsoverdue-button" type="button" class="btn">Overdue</button>
                    </div>
                    <div id="applicationsoverview-container"></div>
                    <div id="applicationsbystatus-container"></div>
                    <div id="applicationsoverdue-container"></div>
                    <div id="applicationsbycenter-container"></div>
                </div>
                <div class="tab-pane" id="traveloverview-tab">
                    <h3>Travel/Rooming Summary</h3>

                    <div id="traveloverview-container"></div>
                </div>
                <div class="tab-pane" id="courses-tab">
                    <h3>Courses</h3>

                    <div class="btn-group" role="group">
                        <button id ="coursesthisweek-button" type="button" class="btn">Completed This Week</button>
                        <button id ="coursesnextmonth-button" type="button" class="btn">Next 4 Weeks</button>
                        <button id ="coursesupcoming-button" type="button" class="btn">Upcoming</button>
                        <button id ="coursescompleted-button" type="button" class="btn">Completed</button>
                        <button id ="coursesguestgames-button" type="button" class="btn">Guest Games</button>
                    </div>
                    <div id="coursesthisweek-container"></div>
                    <div id="coursesnextmonth-container"></div>
                    <div id="coursesupcoming-container"></div>
                    <div id="coursescompleted-container"></div>
                    <div id="coursesguestgames-container"></div>
                </div>
                <div class="tab-pane" id="teammemberstatus-tab">
                    <h3>Team Members of Interest</h3>

                    <div class="btn-group" role="group">
                        <button id ="teammemberstatuswithdrawn-button" type="button" class="btn">Withdrawn</button>
                        <button id ="teammemberstatusctw-button" type="button" class="btn">CTW</button>
                        <button id ="teammemberstatustransfer-button" type="button" class="btn">Transfers</button>
                        <button id ="teammemberstatuspotentials-button" type="button" class="btn">T2 Potentials</button>
                    </div>
                    <div id="teammemberstatuswithdrawn-container"></div>
                    <div id="teammemberstatusctw-container"></div>
                    <div id="teammemberstatustransfer-container"></div>
                    <div id="teammemberstatuspotentials-container"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="loader" style="display: none">
        @include('partials.loading')
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
            'coursesthisweek',
            'coursesnextmonth',
            'coursesupcoming',
            'coursescompleted',
            'coursesguestgames',
            'teammemberstatuswithdrawn',
            'teammemberstatusctw',
            'teammemberstatustransfer',
            'teammemberstatuspotentials',
        ];

        var buttonGroups = [
            [
                'applicationsoverview',
                'applicationsoverdue',
                'applicationsbycenter',
                'applicationsbystatus',
            ],
            [
                'coursesthisweek',
                'coursesnextmonth',
                'coursesupcoming',
                'coursescompleted',
                'coursesguestgames',
            ],
            [
                'teammemberstatuswithdrawn',
                'teammemberstatusctw',
                'teammemberstatustransfer',
                'teammemberstatuspotentials',
            ],
        ];

        $(document).ready(function ($) {
            $('#tabs').tab();

            // Load all of the pages
            $.each(pages, function (index, page) {
                var url = "{{ url("/globalreports/{$globalReport->id}") }}/" + page + "?region={{$region->abbreviation}}";
                var container = "#" + page + "-container";

                // Display loader by default
                $(container).html($("#loader").html());

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

                    // Setup default display
                    if (j == 0) {
                        $(primaryButton).addClass('btn-primary');
                        $(primaryContainer).show();
                    } else {
                        $(primaryButton).addClass('btn-default');
                        $(primaryContainer).hide();
                    }
                });
            });
        });
    </script>
@endsection
