<div class="table-responsive">
    <br/>
    <h4>Applications Overview</h4>
    <table class="table table-condensed table-striped table-hover applicationTable">
        <thead>
        <tr>
            <th rowspan="2" class="border-right">Center</th>
            <th colspan="4" class="data-point border-right">T1 Applications</th>
            <th colspan="4" class="data-point border-right">T2 Applications</th>
            <th colspan="4" class="data-point">Total Team Next Quarter</th>
        </tr>
        <tr>
            <th class="data-point">Out</th>
            <th class="data-point">In</th>
            <th class="data-point">Appr</th>
            <th class="data-point border-right">Wd</th>
            <th class="data-point">Out</th>
            <th class="data-point">In</th>
            <th class="data-point">Appr</th>
            <th class="data-point border-right">Wd</th>
            <th class="data-point">Team 1</th>
            <th class="data-point">Team 2</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <?php
            $t1Total = 0;
            if (isset($centerData['team1']['incoming'])) {
                $t1Total += $centerData['team1']['incoming'];
            }
            if (isset($centerData['team1']['ongoing'])) {
                $t1Total += $centerData['team1']['ongoing'];
            }

            $t2Total = 0;
            if (isset($centerData['team2']['incoming'])) {
                $t2Total += $centerData['team2']['incoming'];
            }
            if (isset($centerData['team2']['ongoing'])) {
                $t2Total += $centerData['team2']['ongoing'];
            }

            $t1TotalClass = '';
            if ($t1Total < 6) {
                $t1TotalClass = 'bg-danger';
            } else if ($t1Total == 6) {
                $t1TotalClass = 'bg-warning';
            }

            $t2TotalClass = '';
            if ($t2Total < 1) {
                $t2TotalClass = 'bg-danger';
            } else if ($t2Total == 1) {
                $t2TotalClass = 'bg-warning';
            }
            ?>
            <tr>
                <td class="border-right">
                    @statsReportLink($statsReports[$centerName], '/TeamExpansion/TmlpRegistrations')
                        {{ $centerName }}
                    @endStatsReportLink
                </td>
                @foreach ($centerData as $team => $registrationData)
                    @if (isset($registrationData['applications']['out']))
                        <td class="data-point success"><strong>{{ $registrationData['applications']['out'] }}</strong></td>
                    @else
                        <td class="data-point">-</td>
                    @endif
                    @if (isset($registrationData['applications']['waiting']))
                        <td class="data-point success"><strong>{{ $registrationData['applications']['waiting'] }}</strong></td>
                    @else
                        <td class="data-point">-</td>
                    @endif
                    <td class="data-point">{{ isset($registrationData['applications']['approved']) ? $registrationData['applications']['approved'] : '-' }}</td>
                    <td class="data-point border-right">{{ isset($registrationData['applications']['withdrawn']) ? $registrationData['applications']['withdrawn'] : '-' }}</td>
                @endforeach
                <td class="{{ $t1TotalClass }} data-point">{{ $t1Total }}</td>
                <td class="{{ $t2TotalClass }} data-point">{{ $t2Total }}</td>
            </tr>
        @endforeach
        </tbody>
        {{-- This is pretty janky, but putting this row outside of the tbody causes datatables from including it in the sort --}}
        <tr style="font-weight:bold">
            <td class="border-right">Totals</td>
            @foreach ($teamCounts as $team => $registrationData)
                <td class="data-point">{{ isset($registrationData['applications']['out']) ? $registrationData['applications']['out'] : '-' }}</td>
                <td class="data-point">{{ isset($registrationData['applications']['waiting']) ? $registrationData['applications']['waiting'] : '-' }}</td>
                <td class="data-point">{{ isset($registrationData['applications']['approved']) ? $registrationData['applications']['approved'] : '-' }}</td>
                <td class="data-point border-right">{{ isset($registrationData['applications']['withdrawn']) ? $registrationData['applications']['withdrawn'] : '-' }}</td>
            @endforeach
            <td class="data-point">
                <?php
                $total = 0;
                if (isset($teamCounts['team1']['incoming'])) {
                    $total += $teamCounts['team1']['incoming'];
                }
                if (isset($teamCounts['team1']['ongoing'])) {
                    $total += $teamCounts['team1']['ongoing'];
                }
                echo $total;
                ?>
            </td>
            <td class="data-point">
                <?php
                $total = 0;
                if (isset($teamCounts['team2']['incoming'])) {
                    $total += $teamCounts['team2']['incoming'];
                }
                if (isset($teamCounts['team2']['ongoing'])) {
                    $total += $teamCounts['team2']['ongoing'];
                }
                echo $total;
                ?>
            </td>
        </tr>
    </table>
</div>
