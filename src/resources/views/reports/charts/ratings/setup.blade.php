<?php
    use TmlpStats\Quarter;

    $divId = isset($divId) ? $divId : 'rating-container';

    $display = (isset($statsReport) || (isset($globalReport) && isset($region)));

    if (isset($statsReport)) {
        $report = $statsReport;
        $quarter = $report->quarter;
        $requestClass = 'LocalReport';
    } else if (isset($globalReport) && isset($regions)) {
        $report = $globalReport;
        $quarter = Quarter::getQuarterByDate($report->reportingDate, $regions[0]->getParentGlobalRegion());
        $requestClass = 'GlobalReport';
    }
?>
@if ($display)
<script>
    var chartData = [];
    var seriesNames = [];

    function updateChart(reportData, seriesName) {
        chartData.push(reportData);
        seriesNames.push(seriesName);

        var series = [];
        var now = new Date();
        for (var i = 0; i < chartData.length; i++) {
            reportData = chartData[i];

            var data = [];
            for (var key in reportData) {
                if (!reportData.hasOwnProperty(key)) continue;

                var date = Date.parse(key);
                if (now < date) continue;

                var obj = reportData[key];

                data.push([Date.parse(key), obj.points.total]);
            }

            series.push({
                name: seriesNames[i],
                data: data
            });
        }

        $("#{{ $divId }}").highcharts({
            chart: {
                type: 'spline'
            },
            title: {
                text: 'Ratings by Week'
            },
            subtitle: {
                text: 'From beginning of quarter starting {{ $quarter->getQuarterStartDate()->format('M j, Y') }}'
            },
            credits: {enabled: false},
            xAxis: {
                type: 'datetime',
                dateTimeLabelFormats: {
                    month: '%e %b',
                    year: '%b'
                }
            },
            yAxis: {
                title: {
                    text: 'Points'
                },
                min: 0,
                plotBands: [{
                    from: 0,
                    to: 9,
                    color: 'rgba(255, 125, 125, 0.2)',
                    label: {
                        text: 'Ineffective'
                    }
                },{
                    from: 9,
                    to: 16,
                    color: 'rgba(255, 207, 125, 0.2)',
                    label: {
                        text: 'Marginally Effective'
                    }
                },{
                    from: 16,
                    to: 22,
                    color: 'rgba(106, 220, 0, 0.2)',
                    label: {
                        text: 'Effective'
                    }
                },{
                    from: 22,
                    to: 28,
                    color: 'rgba(10, 200, 0, 0.2)',
                    label: {
                        text: 'High Performing'
                    }
                }]
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
                pointFormat: "{point.x:%e %b}: {point.y:0f} points"
            },
            plotOptions: {
                spline: {
                    marker: {
                        enabled: true
                    }
                }
            },
            series: series
        });
    }

    $(function() {
        @if ($requestClass == 'LocalReport')
            {{-- This should always use the api directly. Right now it's too slow with all of the other ajax requests --}}
            @if (isset($statsReportData))
                var data = {!! json_encode($statsReportData) !!};
                updateChart(data, "{{ $statsReport->center->name }}");
            @else
                <?php
                $requestJson = "{localReport: {$statsReport->id}}";
                ?>
                Tmlp.Api.LocalReport.getQuarterScoreboard({!! $requestJson !!}).then(function (reportData) {
                    updateChart(reportData, "{{ $statsReport->center->name }}");
                });
            @endif
        @else
            @foreach ($regions as $region)
                {{-- This should always use the api directly. Right now it's too slow with all of the other ajax requests --}}
                @if (isset($regionsData[$region->abbreviation]))
                    var data = {!! json_encode($regionsData[$region->abbreviation]) !!};
                    updateChart(data, @json($region->name));
                @else
                    <?php
                    $requestJson = "{globalReport: {$globalReport->id}, region: {$region->id}}";
                    ?>
                    Tmlp.Api.GlobalReport.getQuarterScoreboard({!! $requestJson !!}).then(function (reportData) {
                        updateChart(reportData, @json($region->name));
                    });
                @endif
            @endforeach
        @endif
    });
</script>
@endif
