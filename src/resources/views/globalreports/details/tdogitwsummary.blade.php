<?php
    $verb = ($game === 'gitw') ? 'Effective' : 'Attending';
?>
<div class="table-responsive">
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th rowspan="2" class="border-right">Center</th>
            <th colspan="3" class="data-point border-right">Team 1</th>
            <th colspan="3" class="data-point border-right">Team 2</th>
            <th colspan="3" class="data-point border-right">Total</th>
        </tr>
        <tr>
            <th class="data-point">Total Members</th>
            <th class="data-point border-right">{{ $verb }}</th>
            <th class="data-point border-right">%</th>
            <th class="data-point">Total Members</th>
            <th class="data-point border-right">{{ $verb }}</th>
            <th class="data-point border-right">%</th>
            <th class="data-point">Total Members</th>
            <th class="data-point border-right">{{ $verb }}</th>
            <th class="data-point border-right">%</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <?php
            $t1TotalClass = '';
            if ($centerData['team1']['total'] == $centerData['team1']['attended']) {
                $t1TotalClass = 'bg-success';
            }

            $t2TotalClass = '';
            if ($centerData['team2']['total'] == $centerData['team2']['attended']) {
                $t2TotalClass = 'bg-success';
            }
            ?>
            <tr>
                <td class="border-right">
                    @statsReportLink($statsReports[$centerName])
                    {{ $centerName }}
                    @endStatsReportLink
                </td>
                <td class="data-point">{{ $centerData['team1']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['team1']['attended'] }}</td>
                <td class="{{ $t1TotalClass }} data-point border-right">
                    @if ($centerData['team1']['total'])
                        {{ $centerData['team1']['percent'] }}%
                    @else
                        -
                    @endif
                </td>
                <td class="data-point">{{ $centerData['team2']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['team2']['attended'] }}</td>
                <td class="{{ $t2TotalClass }} data-point border-right">
                    @if ($centerData['team2']['total'])
                        {{ $centerData['team2']['percent'] }}%
                    @else
                        -
                    @endif
                </td>
                <td class="data-point">{{ $centerData['total']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['total']['attended'] }}</td>
                <td class="{{ $t2TotalClass }} data-point border-right">
                    @if ($centerData['total']['total'])
                        {{ $centerData['total']['percent'] }}%
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="border-right border-bottom">Region</th>
            <th class="data-point border-bottom">{{ $totals['team1']['total'] }}</th>
            <th class="data-point border-right border-bottom">{{ $totals['team1']['attended'] }}</th>
            <th class="data-point border-right border-bottom">
                @if ($totals['team1']['total'])
                    {{ $totals['team1']['percent'] }}%
                @else
                    -
                @endif
            </th>
            <th class="data-point border-bottom">{{ $totals['team2']['total'] }}</th>
            <th class="data-point border-right border-bottom">{{ $totals['team2']['attended'] }}</th>
            <th class="data-point border-right border-bottom">
                @if ($totals['team2']['total'])
                    {{ $totals['team2']['percent'] }}%
                @else
                    -
                @endif
            </th>
            <th class="data-point border-bottom">{{ $totals['total']['total'] }}</th>
            <th class="data-point border-right border-bottom">{{ $totals['total']['attended'] }}</th>
            <th class="data-point border-right border-bottom">
                @if ($totals['total']['total'])
                    {{ $totals['total']['percent'] }}%
                @else
                    -
                @endif
            </th>
        </tr>
    </table>
</div>
