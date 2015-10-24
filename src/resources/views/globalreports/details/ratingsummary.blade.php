<?php
    $ratingColors = [
        '#FF0000',
        '#FF1100',
        '#FF2200',
        '#FF3300',
        '#FF4400',
        '#FF5500',
        '#FF6600',
        '#FF7700',
        '#FF8800',
        '#FF9900',
        '#FFAA00',
        '#FFBB00',
        '#FFCC00',
        '#FFDD00',
        '#FFEE00',
        '#FFFF00',
        '#EEFF00',
        '#DDFF00',
        '#CCFF00',
        '#BBFF00',
        '#AAFF00',
        '#99FF00',
        '#88FF00',
        '#77FF00',
        '#66FF00',
        '#55FF00',
        '#44FF00',
        '#33FF00',
        '#22FF00',
        '#11FF00',
        '#00FF00',
    ];
?>

@if (!$rows)
    <p>No raitings information available.</p>
@else
<div class="table-responsive">
    <h4>{{ $summary['rating'] }} - {{ $summary['points'] }} points</h4>
    <table class="table table-condensed table-bordered ratingsTable" style="width: 700px">
        <thead>
        <th style="text-align: center; width: 10em;">Category</th>
        <th style="text-align: center;">Points</th>
        <th style="text-align: left; width: 10em;">Center</th>
        </thead>
        <tbody>
        @foreach ($rows as $rating => $statsReports)
            <?php $count = 0; ?>
            @foreach ($statsReports as $report)
                <tr class="points">
                    @if ($count === 0)
                        <?php $count++; ?>
                        <td style="vertical-align: middle; text-align: center;" rowspan="{{ count($rows[$rating]) }}">{{ $rating }}</td>
                    @endif
                    <td style="background-color: {{ $ratingColors[$report->getPoints()] }}; vertical-align: middle; text-align: center; font-weight: bold;">
                        <a href="{{ url("/statsreports/{$report->id}") }}">
                        <div class="meter">
                            <span style="width: {{ round(($report->getPoints()/28)*100) }}%">{{ $report->getPoints() }}</span>
                        </div>
                        </a>
                    </td>
                    <td>
                        <a href="{{ url("/statsreports/{$report->id}") }}">
                            <div style="margin-left: 5px">{{ $report->center->name }}</div>
                        </a>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
@endif
