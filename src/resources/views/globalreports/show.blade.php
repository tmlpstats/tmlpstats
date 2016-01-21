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
                <div class="col-sm-3">
                    @include('partials.forms.regions', ['selectedRegion' => $region->abbreviation, 'includeLocalRegions' => true, 'autoSubmit' => true])
                </div>
                <div class="col-sm-9">
                    @if ($reportToken)
                        <input size="80" type="text" value="{{ url($reportToken->getUrl()) }}" id="reportTokenUrl" />
                    @endif
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        <br /><br />
        <div class="row">
            <ul id="tabs" class="nav nav-tabs tabs-top brief-tabs" data-tabs="tabs">
                <li class="active"><a href="#ratingsummary" data-toggle="tab"><span class="long">Ratings </span>Summary</a></li>
                <li><a href="#regionalstats" data-toggle="tab"><span class="long">Regional </span>Games</a></li>
                <li><a href="#statsreports" data-toggle="tab"><span class="long">Center Reports</span><span class="brief">Centers</span></a></li>
                <li><a href="#applications" data-toggle="tab">Applications</a></li>
                <li><a href="#traveloverview" data-toggle="tab">Travel<span class="long"> Summary</span></a></li>
                <li><a href="#courses" data-toggle="tab">Courses</a></li>
                <li><a href="#teammemberstatus" data-toggle="tab">Team Members</a></li>
                <li><a href="#tdosummary" data-toggle="tab"><span class="long">Training &amp; Development</span><span class="brief">TDO</span></a></li>
                <li><a href="#potentials" data-toggle="tab">Potentials</a></li>
            </ul>
        </div>
        <div class="row">
            <div class="tab-content">
                <div class="tab-pane active" id="ratingsummary">
                    <h3>Ratings Summary</h3>

                    <div id="ratingsummary-container"></div>
                </div>
                <div class="tab-pane" id="regionalstats">
                    <h3>Regional Games</h3>

                    <div class="btn-group" role="group">
                        <button id ="regionalstats-button" type="button" class="btn">Scoreboard</button>
                        <button id ="gamesbycenter-button" type="button" class="btn">By Center</button>
                        @if ($globalReport->reportingDate->gte($quarter->classroom2Date))
                        <button id ="repromisesbycenter-button" type="button" class="btn">Repromises</button>
                        @endif
                    </div>

                    <div id="regionalstats-container"></div>
                    <div id="gamesbycenter-container"></div>
                    @if ($globalReport->reportingDate->gte($quarter->classroom2Date))
                    <div id="repromisesbycenter-container"></div>
                    @endif
                </div>
                <div class="tab-pane" id="statsreports">
                    <h3>Center Reports</h3>

                    <div id="statsreports-container"></div>
                </div>
                <div class="tab-pane" id="applications">
                    <h3>Applications</h3>

                    <div class="btn-group" role="group">
                        <button id ="applicationsoverview-button" type="button" class="btn">Overview</button>
                        <button id ="applicationsbystatus-button" type="button" class="btn">By Status</button>
                        <button id ="applicationsbycenter-button" type="button" class="btn">By Center</button>
                        <button id ="applicationst2fromweekend-button" type="button" class="btn" title="Shows Team 2 that registered at the previous TMLP weekend and their current status">T2 Reg. at Weekend</button>
                        <button id ="applicationsoverdue-button" type="button" class="btn">Overdue</button>
                    </div>
                    <div id="applicationsoverview-container"></div>
                    <div id="applicationsbystatus-container"></div>
                    <div id="applicationsoverdue-container"></div>
                    <div id="applicationsbycenter-container"></div>
                    <div id="applicationst2fromweekend-container"></div>
                </div>
                <div class="tab-pane" id="traveloverview">
                    <h3>Travel/Rooming Summary</h3>

                    <div id="traveloverview-container"></div>
                </div>
                <div class="tab-pane" id="courses">
                    <h3>Courses</h3>

                    <div class="btn-group" role="group">
                        <button id ="coursesthisweek-button" type="button" class="btn">Completed This Week</button>
                        <button id ="coursesnextmonth-button" type="button" class="btn">Next 5 Weeks</button>
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
                <div class="tab-pane" id="teammemberstatus">
                    <h3>Team Members of Interest</h3>

                    <div class="btn-group" role="group">
                        <button id ="teammemberstatusctw-button" type="button" class="btn">CTW</button>
                        <button id ="teammemberstatustransfer-button" type="button" class="btn">Transfers</button>
                        <button id ="teammemberstatuswithdrawn-button" type="button" class="btn">Withdrawn</button>
                    </div>
                    <div id="teammemberstatusctw-container"></div>
                    <div id="teammemberstatustransfer-container"></div>
                    <div id="teammemberstatuswithdrawn-container"></div>
                </div>
                <div class="tab-pane" id="tdosummary">
                    <h3>Training and Development Opportunities</h3>
                    <div id="tdosummary-container"></div>
                </div>
                <div class="tab-pane" id="potentials">
                    <h3>Team 2 Potentials</h3>

                    <div class="btn-group" role="group">
                        <button id ="potentialsoverview-button" type="button" class="btn">Overview</button>
                        <button id ="potentialsdetails-button" type="button" class="btn">Details</button>
                    </div>
                    <div id="potentialsoverview-container"></div>
                    <div id="potentialsdetails-container"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="loader" style="display: none">
        @include('partials.loading')
    </div>

    <script type="text/javascript">
        var pages = [
            'ratingsummary',
            'regionalstats',
            'statsreports',
            'applicationsbystatus',
            'applicationsbycenter',
            'applicationsoverdue',
            'applicationsoverview',
            'applicationst2fromweekend',
            'traveloverview',
            'tdosummary',
            'regionalstats',
            'gamesbycenter',
            @if ($globalReport->reportingDate->gte($quarter->classroom2Date))
            'repromisesbycenter',
            @endif
        ];

        var batchedPages = [
            [
                'coursesall',
                'coursesthisweek',
                'coursesnextmonth',
                'coursesupcoming',
                'coursescompleted',
                'coursesguestgames',
            ],
            [
                'teammemberstatusall',
                'teammemberstatuswithdrawn',
                'teammemberstatusctw',
                'teammemberstatustransfer',
                'potentialsoverview',
                'potentialsdetails',
            ],
        ];

        var buttonGroups = [
            [
                'applicationsoverview',
                'applicationsoverdue',
                'applicationsbycenter',
                'applicationsbystatus',
                'applicationst2fromweekend',
            ],
            [
                'coursesthisweek',
                'coursesnextmonth',
                'coursesupcoming',
                'coursescompleted',
                'coursesguestgames',
            ],
            [
                'teammemberstatusctw',
                'teammemberstatustransfer',
                'teammemberstatuswithdrawn',
                'teammemberstatuspotentials',
            ],
            [
                'potentialsoverview',
                'potentialsdetails',
            ],
            [
                'regionalstats',
                'gamesbycenter',
                @if ($globalReport->reportingDate->gte($quarter->classroom2Date))
                'repromisesbycenter',
                @endif
            ],
        ];

        $(document).ready(function ($) {
            $('.nav-tabs').stickyTabs();

            // Load all of the pages
            $.each(pages, function (index, page) {
                var url = "{{ url("/globalreports/{$globalReport->id}") }}/" + page + "?region={{$region->abbreviation}}";
                var container = "#" + page + "-container";

                // Display loader by default
                $(container).html($("#loader").html());

                $.get(url, function (response) {
                    $(container).html(response);
                    updateDates();
                    initDataTables();
                }).fail(function (jqXHR) {
                    var message = getErrorMessage(jqXHR.status);
                    $(container).html('<br/><p>' + message + '</p>');
                });
            });

            $.each(batchedPages, function (index, batch) {
                var query = batch.shift();
                var url = "{{ url("/globalreports/{$globalReport->id}") }}/" + query + "?region={{$region->abbreviation}}";

                $.each(batch, function (i, name) {
                    $("#" + name + "-container").html($("#loader").html());
                });

                $.get(url, function (response) {
                    $.each(response, function (name, data) {
                        $("#" + name + "-container").html(data);
                        updateDates();
                        initDataTables();
                    });
                }).fail(function (jqXHR) {
                    var message = getErrorMessage(jqXHR.status);
                    $.each(batch, function (i, name) {
                        $("#" + name + "-container").html('<br/><p>' + message + '</p>');
                    });
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
