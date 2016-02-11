<br>
<h5>
    Registrations Per Participant
</h5>
<div class="table-responsive">
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th rowspan="2" class="border-right" style="vertical-align: middle">Center</th>
            @foreach ($games as $game)
                <th colspan="4" class="data-point border-right">{{ strtoupper($game) }}</th>
            @endforeach
            <th colspan="2" class="data-point border-right">Total (All Games)</th>
        </tr>
        <tr>
            @foreach ($games as $game)
                <th class="data-point" title="Promise">P</th>
                <th class="data-point" title="Actual">A</th>
                <th class="data-point" title="Registrations Per Participant this week">RPP (week)</th>
                <th class="data-point border-right" title="Registrations Per Participant this quarter">RPP (quarter)</th>
            @endforeach
            <th class="data-point" title="Total Registrations Per Participant this week">RPP (week)</th>
            <th class="data-point border-right" title="Total Registrations Per Participant this quarter">RPP (quarter)</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <th class="border-right">
                    @statsReportLink($centerData['statsReport'])
                    {{ $centerName }}
                    @endStatsReportLink
                </th>
                @foreach ($games as $game)
                    <?php
                        $actualClass = ($centerData['promise'][$game] > $centerData['actual'][$game])
                            ? 'bg-danger'
                            : 'success';
                    ?>
                    <td class="data-point">{{ $centerData['promise'][$game] }}</td>
                    <td class="data-point {{ $actualClass }}">
                        {{ isset($centerData['actual']) ? $centerData['actual'][$game] : '&nbsp;' }}
                    </td>
                    <td class="data-point">{{ $centerData['rpp']['week'][$game] }}</td>
                    <td class="data-point border-right">{{ $centerData['rpp']['quarter'][$game] }}</td>
                @endforeach
                <td class="data-point">{{ $centerData['rpp']['week']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['rpp']['quarter']['total'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
