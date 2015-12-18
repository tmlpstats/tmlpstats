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
                    <input size="80" type="text" value="{{ url($reportToken->getUrl()) }}" id="reportTokenUrl" />
                @endif
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <br />
    <br />

    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#summary" data-toggle="tab">Weekly Summary</a></li>
            <li><a href="#overview" data-toggle="tab">Report Details</a></li>
            <li><a href="#centerstats" data-toggle="tab">Center Games</a></li>
            <li><a href="#classlist" data-toggle="tab">Team Members</a></li>
            <li><a href="#tmlpregistrations" data-toggle="tab">Team Expansion</a></li>
            <li><a href="#courses" data-toggle="tab">Courses</a></li>
            @can ('readContactInfo', $statsReport)
            <li><a href="#contactinfo" data-toggle="tab">Contact Info</a></li>
            @endcan
            @if ($statsReport->reportingDate->eq($statsReport->quarter->firstWeekDate))
            <li><a href="#transitionsummary" data-toggle="tab">Transfer Check</a></li>
            @endif
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="summary">
                <h3>Week Summary</h3>
                <div id="summary-container"></div>
            </div>
            <div class="tab-pane" id="overview">
                <h3>Report Details</h3>
                @include('statsreports.details.overview', ['statsReport' => $statsReport, 'sheetUrl' => $sheetUrl])
                <h3>Results</h3>
                <div id="results-container"></div>
            </div>
            <div class="tab-pane" id="centerstats">
                <h3>Center Games</h3>
                <div id="centerstats-container"></div>
            </div>
            <div class="tab-pane" id="classlist">
                <h3>Team Members</h3>
                <div class="btn-group" role="group">
                    <button id ="classlist-button" type="button" class="btn">Summary</button>
                    <button id ="gitwsummary-button" type="button" class="btn">GITW</button>
                    <button id ="tdosummary-button" type="button" class="btn">TDO</button>
                </div>
                <div id="classlist-container"></div>
                <div id="gitwsummary-container"></div>
                <div id="tdosummary-container"></div>
            </div>
            <div class="tab-pane" id="tmlpregistrations">
                <h3>Team Expansion</h3>
                <div class="btn-group" role="group">
                    <button id ="tmlpregistrations-button" type="button" class="btn">By Team Year</button>
                    <button id ="tmlpregistrationsbystatus-button" type="button" class="btn">By Status</button>
                </div>
                <div id="tmlpregistrations-container"></div>
                <div id="tmlpregistrationsbystatus-container"></div>
            </div>
            <div class="tab-pane" id="courses">
                <h3>Courses</h3>
                <div id="courses-container"></div>
            </div>
            @can ('readContactInfo', $statsReport)
            <div class="tab-pane" id="contactinfo">
                <h3>Contact Info</h3>
                <div id="contactinfo-container"></div>
            </div>
            @endcan
            @if ($statsReport->reportingDate->eq($statsReport->quarter->firstWeekDate))
            <div class="tab-pane" id="transitionsummary">
                <h3>Transfer Check</h3>
                <div class="btn-group" role="group">
                    <button id ="peopletransfersummary-button" type="button" class="btn">People</button>
                    <button id ="coursestransfersummary-button" type="button" class="btn">Courses</button>
                </div>
                <div id="peopletransfersummary-container"></div>
                <div id="coursestransfersummary-container"></div>
            </div>
            @endif
        </div>
    </div>

    <div id="loader" style="display: none">
        @include('partials.loading')
    </div>

    <script type="text/javascript">
        var pages = [
            'summary',
            'results',
            'centerstats',
            'classlist',
            'gitwsummary',
            'tdosummary',
            'tmlpregistrations',
            'tmlpregistrationsbystatus',
            'courses',
            @can ('readContactInfo', $statsReport)
            'contactinfo',
            @endcan
            @if ($statsReport->reportingDate->eq($statsReport->quarter->firstWeekDate))
            'peopletransfersummary',
            'coursestransfersummary',
            @endif
        ];

        var buttonGroups = [
            [
                'classlist',
                'gitwsummary',
                'tdosummary',
            ],
            [
                'tmlpregistrations',
                'tmlpregistrationsbystatus',
            ],
            [
                'peopletransfersummary',
                'coursestransfersummary',
            ]
        ];

        $(document).ready(function ($) {
            $('.nav-tabs').stickyTabs();

            $('select.reportSelector').change(function() {
                var baseUrl = "{{ url('statsreports') }}";
                var newReport = $('.reportSelector option:selected').val();
                window.location.replace(baseUrl + '/' + newReport);
            });

            // Load all of the pages
            $.each(pages, function (index, page) {
                var url = "{{ url("/statsreports/{$statsReport->id}") }}/" + page;
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


@else
    <p>Unable to find report.</p>
@endif

@endsection
