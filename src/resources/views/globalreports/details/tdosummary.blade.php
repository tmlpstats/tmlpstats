<div class="table-responsive">
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th rowspan="2" class="border-right">Center</th>
            <th colspan="3" class="data-point border-right">Team 1</th>
            <th colspan="3" class="data-point border-right">Team 2</th>
        </tr>
        <tr>
            <th class="data-point">Current</th>
            <th class="data-point border-right">Attending</th>
            <th class="data-point border-right">%</th>
            <th class="data-point">Current</th>
            <th class="data-point border-right">Attending</th>
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
                        {{ round(($centerData['team1']['attended']/$centerData['team1']['total'])*100) }}%
                    @else
                        -
                    @endif
                </td>
                <td class="data-point">{{ $centerData['team2']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['team2']['attended'] }}</td>
                <td class="{{ $t2TotalClass }} data-point border-right">
                    @if ($centerData['team2']['total'])
                        {{ round(($centerData['team2']['attended']/$centerData['team2']['total'])*100) }}%
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="border-right border-bottom">&nbsp;</th>
            <th class="data-point border-bottom">{{ $totals['team1']['total'] }}</th>
            <th class="data-point border-right border-bottom">{{ $totals['team1']['attended'] }}</th>
            <th class="data-point border-right border-bottom">
                @if ($totals['team1']['total'])
                    {{ round(($totals['team1']['attended']/$totals['team1']['total'])*100) }}%
                @else
                    -
                @endif
            </th>
            <th class="data-point border-bottom">{{ $totals['team2']['total'] }}</th>
            <th class="data-point border-right border-bottom">{{ $totals['team2']['attended'] }}</th>
            <th class="data-point border-right border-bottom">
                @if ($totals['team2']['total'])
                    {{ round(($totals['team2']['attended']/$totals['team2']['total'])*100) }}%
                @else
                    -
                @endif
            </th>
        </tr>
    </table>
</div>
