<div class="table-responsive">
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th rowspan="3" class="border-left border-right border-top">Center</th>
            <th colspan="9" class="data-point border-right border-top">Team 1</th>
            <th colspan="9" class="data-point border-right border-top">Team 2</th>
            <th colspan="9" class="data-point border-right border-top">Total</th>
        </tr>
        <tr>
            <th class="data-point">Members</th>
            <th colspan="2" class="data-point">LF</th>
            <th colspan="2" class="data-point">CAP</th>
            <th colspan="2" class="data-point border-right">CPC</th>
            <th colspan="2" class="data-point border-right">Total</th>

            <th class="data-point">Members</th>
            <th colspan="2" class="data-point">LF</th>
            <th colspan="2" class="data-point">CAP</th>
            <th colspan="2" class="data-point border-right">CPC</th>
            <th colspan="2" class="data-point border-right">Total</th>

            <th class="data-point">Members</th>
            <th colspan="2" class="data-point">LF</th>
            <th colspan="2" class="data-point">CAP</th>
            <th colspan="2" class="data-point border-right">CPC</th>
            <th colspan="2" class="data-point border-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <td class="border-left border-right">
                    @statsReportLink($statsReports[$centerName])
                    {{ $centerName }}
                    @endStatsReportLink
                </td>
                <td class="data-point">{{ $centerData['team1']['member_count'] }}</td>
                <td class="data-point">{{ $centerData['team1']['lf'] }}</td>
                <td class="data-point">{{ $centerData['team1']['lf_rpp'] }}</td>
                <td class="data-point">{{ $centerData['team1']['cap'] }}</td>
                <td class="data-point">{{ $centerData['team1']['cap_rpp'] }}</td>
                <td class="data-point">{{ $centerData['team1']['cpc'] }}</td>
                <td class="data-point border-right">{{ $centerData['team1']['cpc_rpp'] }}</td>
                <td class="data-point">{{ $centerData['team1']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['team1']['total_rpp'] }}</td>

                <td class="data-point">{{ $centerData['team2']['member_count'] }}</td>
                <td class="data-point">{{ $centerData['team2']['lf'] }}</td>
                <td class="data-point">{{ $centerData['team2']['lf_rpp'] }}</td>
                <td class="data-point">{{ $centerData['team2']['cap'] }}</td>
                <td class="data-point">{{ $centerData['team2']['cap_rpp'] }}</td>
                <td class="data-point">{{ $centerData['team2']['cpc'] }}</td>
                <td class="data-point border-right">{{ $centerData['team2']['cpc_rpp'] }}</td>
                <td class="data-point">{{ $centerData['team2']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['team2']['total_rpp'] }}</td>

                <td class="data-point">{{ $centerData['total']['member_count'] }}</td>
                <td class="data-point">{{ $centerData['total']['lf'] }}</td>
                <td class="data-point">{{ $centerData['total']['lf_rpp'] }}</td>
                <td class="data-point">{{ $centerData['total']['cap'] }}</td>
                <td class="data-point">{{ $centerData['total']['cap_rpp'] }}</td>
                <td class="data-point">{{ $centerData['total']['cpc'] }}</td>
                <td class="data-point border-right">{{ $centerData['total']['cpc_rpp'] }}</td>
                <td class="data-point">{{ $centerData['total']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['total']['total_rpp'] }}</td>

            </tr>
        @endforeach
        </tbody>
        <tr>
            <td class="data-point"></td>
            <td class="data-point">{{ $totals['team1']['member_count'] }}</td>
            <td class="data-point">{{ $totals['team1']['lf'] }}</td>
            <td class="data-point">{{ $totals['team1']['lf_rpp'] }}</td>
            <td class="data-point">{{ $totals['team1']['cap'] }}</td>
            <td class="data-point">{{ $totals['team1']['cap_rpp'] }}</td>
            <td class="data-point">{{ $totals['team1']['cpc'] }}</td>
            <td class="data-point border-right">{{ $totals['team1']['cpc_rpp'] }}</td>
            <td class="data-point">{{ $totals['team1']['total'] }}</td>
            <td class="data-point border-right">{{ $totals['team1']['total_rpp'] }}</td>

            <td class="data-point">{{ $totals['team2']['member_count'] }}</td>
            <td class="data-point">{{ $totals['team2']['lf'] }}</td>
            <td class="data-point">{{ $totals['team2']['lf_rpp'] }}</td>
            <td class="data-point">{{ $totals['team2']['cap'] }}</td>
            <td class="data-point">{{ $totals['team2']['cap_rpp'] }}</td>
            <td class="data-point">{{ $totals['team2']['cpc'] }}</td>
            <td class="data-point border-right">{{ $totals['team2']['cpc_rpp'] }}</td>
            <td class="data-point">{{ $totals['team2']['total'] }}</td>
            <td class="data-point border-right">{{ $totals['team2']['total_rpp'] }}</td>

            <td class="data-point">{{ $totals['total']['member_count'] }}</td>
            <td class="data-point">{{ $totals['total']['lf'] }}</td>
            <td class="data-point">{{ $totals['total']['lf_rpp'] }}</td>
            <td class="data-point">{{ $totals['total']['cap'] }}</td>
            <td class="data-point">{{ $totals['total']['cap_rpp'] }}</td>
            <td class="data-point">{{ $totals['total']['cpc'] }}</td>
            <td class="data-point border-right">{{ $totals['total']['cpc_rpp'] }}</td>
            <td class="data-point">{{ $totals['total']['total'] }}</td>
            <td class="data-point border-right">{{ $totals['total']['total_rpp'] }}</td>
        </tr>
    </table>
</div>
