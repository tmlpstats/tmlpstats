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
<div class="table-responsive">
    <h4>{{ $rating }} - {{ $points }} points</h4>
    <table class="table table-condensed table-bordered" style="width: 700px">
        <thead>
        <th style="text-align: center; width: 10em;">Category</th>
        <th style="text-align: center;">Points</th>
        <th style="text-align: left; width: 10em;">Center</th>
        </thead>
        <tbody>
        @foreach ($centerReports as $rating => $statsReports)
            <?php $count = 0; ?>
            @foreach ($statsReports as $report)
                <tr>
                    @if ($count === 0)
                        <?php $count++; ?>
                        <td style="vertical-align: middle; text-align: center;" rowspan="{{ count($centerReports[$rating]) }}">{{ $rating }}</td>
                    @endif
                    <td style="background-color: {{ $ratingColors[$report->getPoints()] }}; vertical-align: middle; text-align: center; font-weight: bold;">{{ $report->getPoints() }}</td>
                    <td>{{ $report->center->name }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
