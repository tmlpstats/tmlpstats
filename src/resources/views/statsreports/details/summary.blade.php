<div class="row">
    <div class="col-md-5">
        @include('reports.centergames.week', compact('reportData'))
    </div>
    <div class="col-md-7">
        <div class="row">
            <div class="col-md-6">
                <div id="tdo-container" style="width: 250px; height: 150px; margin: 0 auto"></div>
            </div>
            <div class="col-md-6">
                <div id="gitw-container" style="width: 250px; height: 150px; margin: 0 auto"></div>
            </div>
        </div>
        <div class="row">
            @if ($statsReport->reportingDate->gte($statsReport->quarter->getClassroom2Date($statsReport->center)))
                <div class="col-md-6">
                    <h4>Travel &amp; Rooming</h4>
                    <dl class="dl-horizontal">
                        <dt>Team Travel:</dt>
                        <dd>{{ $teamTravelDetails['team1']['travel'] + $teamTravelDetails['team2']['travel'] }}
                            / {{ $teamTravelDetails['team1']['total'] + $teamTravelDetails['team2']['total'] }}</dd>
                        <dt>Team Room:</dt>
                        <dd>{{ $teamTravelDetails['team1']['room'] + $teamTravelDetails['team2']['room'] }}
                            / {{ $teamTravelDetails['team1']['total'] + $teamTravelDetails['team2']['total'] }}</dd>
                        <dt>Incoming Travel:</dt>
                        <dd>{{ $incomingTravelDetails['team1']['travel'] + $incomingTravelDetails['team2']['travel'] }}
                            / {{ $incomingTravelDetails['team1']['total'] + $incomingTravelDetails['team2']['total'] }}</dd>
                        <dt>Incoming Room:</dt>
                        <dd>{{ $incomingTravelDetails['team1']['room'] + $incomingTravelDetails['team2']['room'] }}
                            / {{ $incomingTravelDetails['team1']['total'] + $incomingTravelDetails['team2']['total'] }}</dd>
                    </dl>
                </div>
            @endif

            @if ($applications && $applications['total'])
                <div class="col-md-6">
                    <h4>Application Status:</h4>
                    <dl class="dl-horizontal">
                        @if ($applications['notSent'])
                            <dt>Not Sent:</dt>
                            <dd>{{ count($applications['notSent']) }}</dd>
                        @endif
                        @if ($applications['out'])
                            <dt>Out:</dt>
                            <dd>{{ count($applications['out']) }}</dd>
                        @endif
                        @if ($applications['waiting'])
                            <dt>Waiting Interview:</dt>
                            <dd>{{ count($applications['waiting']) }}</dd>
                        @endif
                        @if ($applications['approved'])
                            <dt>Approved:</dt>
                            <dd>{{ count($applications['approved']) }}</dd>
                        @endif
                        @if ($applications['withdrawn'])
                            <dt>Withdrawn:</dt>
                            <dd>{{ count($applications['withdrawn']) }}</dd>
                        @endif
                        <dt>Total:</dt>
                        <dd>{{ $applications['total'] }}</dd>
                    </dl>
                </div>
            @endif

            @if ($teamWithdraws && $teamWithdraws['total'])
                <div class="col-md-6">
                    <h4>Team Members Withdrawn:</h4>
                    <dl class="dl-horizontal">
                        <dt>Team 1:</dt>
                        <dd>{{ $teamWithdraws['team1'] }}</dd>
                        <dt>Team 2:</dt>
                        <dd>{{ $teamWithdraws['team2'] }}</dd>
                        <dt>Total:</dt>
                        <dd>{{ $teamWithdraws['total'] }}</dd>
                        @if ($teamWithdraws['ctw'])
                            <dt>In Conversation:</dt>
                            <dd>{{ $teamWithdraws['ctw'] }}</dd>
                        @endif
                    </dl>
                </div>
            @endif

            @if ($completedCourses)
                <div class="col-md-6">
                    <h4>Course Results:</h4>
                    <dl class="dl-horizontal">
                        @foreach ($completedCourses as $courseData)
                            <span style="text-decoration: underline">{{ $courseData['type'] }}
                                - {{ $courseData['startDate']->format('M j') }}</span>
                            <dl class="dl-horizontal">
                                <dt>Standard Starts:</dt>
                                <dd>{{ $courseData['currentStandardStarts'] }}</dd>
                                <dt>Reg Fulfillment:</dt>
                                <dd>{{ $courseData['completionStats']['registrationFulfillment'] }}%</dd>
                                <dt>Reg Effectiveness:</dt>
                                <dd>{{ $courseData['completionStats']['registrationEffectiveness'] }}%</dd>
                                <dt>Registrations:</dt>
                                <dd>{{ $courseData['registrations'] }}</dd>
                            </dl>
                        @endforeach
                    </dl>
                </div>
            @endif

            @if ($upcomingCourses)
                <div class="col-md-6">
                    <h4>Upcoming Courses:</h4>
                    <dl class="dl-horizontal">
                        @foreach ($upcomingCourses as $courseData)
                            <span style="text-decoration: underline">{{ $courseData['type'] }}
                                - {{ $courseData['startDate']->format('M j') }}</span>
                            <dl class="dl-horizontal">
                                <dt>Standard Starts:</dt>
                                <dd>{{ $courseData['currentStandardStarts'] }}</dd>
                                <dt>Guests Promised:</dt>
                                <dd>{{ (int) $courseData['guestsPromised'] }}</dd>
                                <dt>Guests Invited:</dt>
                                <dd>{{ (int) $courseData['guestsInvited'] }}</dd>
                                <dt>Guests Confirmed:</dt>
                                <dd>{{ (int) $courseData['guestsConfirmed'] }}</dd>
                            </dl>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    var tdoData = [
        <?php
        $tdoTotal = $tdo && $tdo['percent']['total'] > 0
            ? round($tdo['total'] / ($tdo['percent']['total'] / 100))
            : 0;
        $tdoNotPresent = $tdo && $tdo['percent']['total'] > 0
            ? $tdoTotal - $tdo['total']
            : 0;
        ?>
        @if ($tdo)
            ["{{ $tdo['percent']['total'] }}%", {{ $tdo['percent']['total'] }}],
            ["{{ 100 - $tdo['percent']['total'] }}%", {{ 100 - $tdo['percent']['total'] }}],
        @else
            ["0", 0],
            ["0", 100]
        @endif
    ];
    var gitwData = [
        <?php
        $gitwTotal = $gitw && $gitw['percent']['total'] > 0
            ? round($gitw['total'] / ($gitw['percent']['total'] / 100))
            : 0;
        $gitwNotPresent = $gitw && $gitw['percent']['total'] > 0
            ? $gitwTotal - $gitw['total']
            : 0;
        ?>
        @if ($gitw)
            ["{{ $gitw['percent']['total'] }}%", {{ $gitw['percent']['total'] }}],
            ["{{ 100 - $gitw['percent']['total'] }}%", {{ 100 - $gitw['percent']['total'] }}],
        @else
            ["0", 0],
            ["0", 100]
        @endif
    ];

    $(function () {
        var pieTheme,
            pieSeriesTemplate,
            tdoChart,
            tdoSeries,
            gitwChart,
            gitwSeries;

        pieTheme = {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: 0,
                plotShadow: false,
                marginTop: -50,
                marginBottom: -50,
                marginLeft: 0,
                marginRight: 0
            },
            title: {
                align: 'center',
                verticalAlign: 'middle',
                y: 5
            },
            credits: {enabled: false},
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    animation: false,
                    dataLabels: {
                        enabled: true,
                        distance: -16,
                        style: {
                            fontWeight: 'bold',
                            fontSize: '13px'
                        }
                    },
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%'],
                    size: '100%'
                }
            }
        };

        pieSeriesTemplate = {
            type: 'pie',
            innerSize: '70%',
            states: {
                hover: {
                    enabled: false
                }
            },
            colors: ['#98D04F', '#D9D9DD']
        };

        tdoChart = $.extend(true, {}, pieTheme);
        tdoSeries = $.extend(true, {}, pieSeriesTemplate);
        $('#tdo-container').highcharts($.extend(true, tdoChart, {
            title: {
                text: 'Training &<br>Development<br><span style="font-size:small">{{ $tdo['total'] }}/{{ $tdoTotal }}</style>'
            },
            series: [$.extend(true, tdoSeries, {
                name: 'TDO Participation',
                data: tdoData
            })]
        }));

gitwChart = $.extend(true, {}, pieTheme);
gitwSeries = $.extend(true, {}, pieSeriesTemplate);
$('#gitw-container').highcharts($.extend(true, gitwChart, {
    title: {
        text: 'Game in<br>the World<br><span style="font-size:small">{{ $gitw['total'] }}/{{ $gitwTotal }}</style>'
    },
    series: [$.extend(true, gitwSeries, {
        name: 'GITW Effectiveness',
        data: gitwData
    })]
}));
});
</script>
