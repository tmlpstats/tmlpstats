@extends('template')

@section('headers')
    <style type="text/css">
        td.centerStatsFrame {
            padding: 10px;
        }
        table.centerStatsTable {
            width: 95%;
        }
        .centerStatsTable th, .centerStatsTable td {
            text-align: center;
        }
    </style>
@endsection

@section('content')

@if ($statsReport)
    <h2>{{ $statsReport->center->name }} - {{ $statsReport->reportingDate->format('F j, Y') }}</h2>
    <a href="{{ url('/home') }}"><< See All</a><br/><br/>

    <div class="table-responsive">
        <table class="table table-condensed table-striped">
            <tr>
                <th>Center:</th>
                <td>{{ $statsReport->center->name }}</td>
            </tr>
            <tr>
                <th>Region:</th>
                <td>
                    <?php
                    $region = $statsReport->center->getLocalRegion();
                    if ($region) {
                        echo $region->name;
                    }
                    ?>
                    @if ($statsReport->center->getLocalRegion())
                        - <?php
                        $region = $statsReport->center->getLocalRegion();
                        if ($region) {
                            echo $region->name;
                        }
                        ?>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Stats Email:</th>
                <td>{{ $statsReport->center->statsEmail }}</td>
            </tr>
            <tr>
                <th>Submitted At:</th>
                <td><?php
                    if ($statsReport->submittedAt) {
                        $submittedAt = clone $statsReport->submittedAt;
                        $submittedAt->setTimezone($statsReport->center->timezone);
                        echo $submittedAt->format('l, F jS \a\t g:ia T');
                    } else {
                        echo '-';
                    }
                    ?></td>
            </tr>
            <tr>
                <th>Submitted Sheet Version:</th>
                <td>{{ $statsReport->version }}</td>
            </tr>
            <tr>
                <th>Rating:</th>
                <td>
                    @if ($statsReport && $statsReport->getPoints() !== null)
                        {{ $statsReport->getRating() }} ({{ $statsReport->getPoints() }})
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <th>File:</th>
                <td>
                    @if ($sheetUrl)
                        <a href="{{ $sheetUrl }}">Download</a>
                    @else
                        <span style="font-style: italic">Sheet not available</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Submission Comment:</th>
                <td>{{ $statsReport->submitComment }}</td>
            </tr>
        </table>
    </div>

    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#centerstats-tab" data-toggle="tab">Center Stats</a></li>
            <li><a href="#tmlpregistrations-tab" data-toggle="tab">TMLP Registrations</a></li>
            <li><a href="#classlist-tab" data-toggle="tab">Class List</a></li>
            <li><a href="#courses-tab" data-toggle="tab">Courses</a></li>
            <li><a href="#contactinfo-tab" data-toggle="tab">Contact Info</a></li>
            <li><a href="#results-tab" data-toggle="tab">Validation Results</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="centerstats-tab">
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
                    @if ($sheetUrl)
                        <div id="updating">
                            <div class="loader">
                                <div class="duo duo1">
                                    <div class="dot dot-a"></div>
                                    <div class="dot dot-b"></div>
                                </div>
                                <div class="duo duo2">
                                    <div class="dot dot-a"></div>
                                    <div class="dot dot-b"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p>Results not available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#tabs').tab();

            @if ($sheetUrl)
            $.ajax({
                type: "GET",
                url: "{{ url('/statsreports/' . $statsReport->id . '/results') }}",
                success: function(response) {
                    $("#results-container").html(response);
                }
            });
            @endif
        });
    </script>

@else
    <p>Unable to find report.</p>
@endif

@endsection
