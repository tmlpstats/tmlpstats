<div class="table-responsive">
    <h4>Applications Overview</h4>
    <table class="table table-condensed table-striped table-hover applicationTable">
        <thead>
        <tr>
            <th rowspan="2" style="border-right: 2px solid #DDD;">Center</th>
            <th colspan="4" style="text-align: center; border-right: 2px solid #DDD;">T1 Applications</th>
            <th colspan="4" style="text-align: center; border-right: 2px solid #DDD;">T2 Applications</th>
            <th colspan="4" style="text-align: center">Total Team Next Quarter</th>
        </tr>
        <tr>
            <th style="text-align: center">Out</th>
            <th style="text-align: center">In</th>
            <th style="text-align: center">Appr</th>
            <th style="text-align: center; border-right: 2px solid #DDD;">Wd</th>
            <th style="text-align: center">Out</th>
            <th style="text-align: center">In</th>
            <th style="text-align: center">Appr</th>
            <th style="text-align: center; border-right: 2px solid #DDD;">Wd</th>
            <th style="text-align: center">Team 1</th>
            <th style="text-align: center">Team 2</th>
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
            } else if ($t1Total <= 7) {
                $t1TotalClass = 'bg-warning';
            }

            $t2TotalClass = '';
            if ($t2Total < 1) {
                $t2TotalClass = 'bg-danger';
            } else if ($t2Total <= 2) {
                $t2TotalClass = 'bg-warning';
            }
            ?>
            <tr>
                <td style="border-right: 2px solid #DDD;">{{ $centerName }}</td>
                @foreach ($centerData as $team => $registrationData)
                    <td style="text-align: center">{{ isset($registrationData['applications']['out']) ? $registrationData['applications']['out'] : 0 }}</td>
                    <td style="text-align: center">{{ isset($registrationData['applications']['waiting']) ? $registrationData['applications']['waiting'] : 0 }}</td>
                    <td style="text-align: center">{{ isset($registrationData['applications']['approved']) ? $registrationData['applications']['approved'] : 0 }}</td>
                    <td style="text-align: center; border-right: 2px solid #DDD;">{{ isset($registrationData['applications']['withdrawn']) ? $registrationData['applications']['withdrawn'] : 0 }}</td>
                @endforeach
                <td class="{{ $t1TotalClass }}" style="text-align: center">{{ $t1Total }}</td>
                <td class="{{ $t2TotalClass }}" style="text-align: center">{{ $t2Total }}</td>
            </tr>
        @endforeach
        </tbody>
        {{-- This is pretty janky, but putting this row outside of the tbody causes datatables from including it in the sort --}}
        <tr style="font-weight:bold">
            <td style="border-right: 2px solid #DDD;">Totals</td>
            @foreach ($teamCounts as $team => $registrationData)
                <td style="text-align: center">{{ isset($registrationData['applications']['out']) ? $registrationData['applications']['out'] : 0 }}</td>
                <td style="text-align: center">{{ isset($registrationData['applications']['waiting']) ? $registrationData['applications']['waiting'] : 0 }}</td>
                <td style="text-align: center">{{ isset($registrationData['applications']['approved']) ? $registrationData['applications']['approved'] : 0 }}</td>
                <td style="text-align: center; border-right: 2px solid #DDD;">{{ isset($registrationData['applications']['withdrawn']) ? $registrationData['applications']['withdrawn'] : 0 }}</td>
            @endforeach
            <td style="text-align: center">
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
            <td style="text-align: center">
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
