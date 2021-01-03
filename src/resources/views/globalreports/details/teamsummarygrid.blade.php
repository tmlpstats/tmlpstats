<?php
    $cols = [
        'qtrPromise',
        'registrations.total',
        'registrations.net',
        'wkndReg.before',
        'wkndReg.after',
        'appStatus.appOut',
        'appStatus.appIn',
        'appStatus.appr',
        'appStatus.wd',
        'appStatusNext.appOut',
        'appStatusNext.appIn',
        'appStatusNext.appr',
        'appStatusNext.wd',
        'onTeamAtWknd',
        'xferOut',
        'xferIn',
        'withdraws.q1',
        'withdraws.q2',
        'withdraws.q3',
        'withdraws.q4',
        'withdraws.all',
        'wbo',
        'ctw',
        'rereg',
        'currentOnTeam',
        'tdo',
        'completing',
        'onTeamNextQtr',
        'attendingWeekend',
    ];
    if ($teamYear == 2) {
        $cols = [
            'qtrPromise',
            'registrations.total',
            'registrations.net',
            'wkndReg.before',
            'wkndReg.during',
            'wkndReg.after',
            'appStatus.appOut',
            'appStatus.appIn',
            'appStatus.appr',
            'appStatus.wd',
            'appStatusNext.appOut',
            'appStatusNext.appIn',
            'appStatusNext.appr',
            'appStatusNext.wd',
            'onTeamAtWknd',
            'xferOut',
            'xferIn',
            'withdraws.q1',
            'withdraws.q2',
            'withdraws.q3',
            'withdraws.q4',
            'withdraws.all',
            'wbo',
            'ctw',
            'rereg',
            'currentOnTeam',
            'tdo',
            'completing',
            'onTeamNextQtr',
            'attendingWeekend',
        ];
    }

    $originals = []; // getOfficialData($teamYear);

    // check if function exists incase both grids load in the same request
    if (!function_exists('getClasses')) {
        function getClasses($name, $centerName, $reportData, $originals, $teamYear) {
            $classes = [];
            switch ($name) {
                case 'centerName':
                    $classes[] = 'border-right';
                    break;
                case 'registrations.total':
                case 'wkndReg.before':
                case 'wkndReg.during':
                case 'appStatus.appOut':
                case 'appStatus.appIn':
                case 'appStatus.appr':
                case 'appStatusNext.appOut':
                case 'appStatusNext.appIn':
                case 'appStatusNext.appr':
                case 'withdraws.q1':
                case 'withdraws.q2':
                case 'withdraws.q3':
                case 'withdraws.q4':
                    $classes[] = 'data-point';
                    break;
                default:
                    $classes[] = 'data-point';
                    $classes[] = 'border-right';
                    break;
            }

            if ($centerName !== 'totals') {
                switch ($name) {
                    case 'centerName':
                    case 'onTeamNextQtr':
                        $onTeam = array_get($reportData, "{$centerName}.onTeamNextQtr");
                        if ($teamYear == 1 && $onTeam <= 7 || $teamYear == 2 && $onTeam <= 1) {
                            $classes[] = 'warning';
                        }
                        break;
                    case 'withdraws.q1':
                    case 'withdraws.q2':
                    case 'withdraws.q3':
                    case 'withdraws.q4':
                    case 'withdraws.all':
                    case 'ctw':
                        $value = array_get($reportData, "{$centerName}.{$name}");
                        if ($value >= 1) {
                            $classes[] = 'warning';
                        } else {
                            $classes[] = 'info-lite';
                        }
                        break;

                    case 'appStatus.appOut':
                    case 'appStatus.appIn':
                    case 'appStatus.appr':
                    case 'appStatus.wd':
                    case 'appStatusNext.appOut':
                    case 'appStatusNext.appIn':
                    case 'appStatusNext.appr':
                    case 'appStatusNext.wd':
                        $classes[] = 'info-lite';
                        break;

                    case 'qtrPromise':
                        $qtrPromise = array_get($reportData, "{$centerName}.qtrPromise");
                        $completing = array_get($reportData, "{$centerName}.completing");
                        $wds = array_get($reportData, "{$centerName}.withdraws.all");
                        $wbo = array_get($reportData, "{$centerName}.wbo");
                        if (($qtrPromise - $completing - $wds - $wbo) < 1) {
                            $classes[] = 'warning';
                        }
                        break;
                }
            } else {
                switch ($name) {
                    case 'xferOut':
                    case 'xferIn':
                        $xferOut = array_get($reportData, "{$centerName}.xferOut");
                        $xferIn = array_get($reportData, "{$centerName}.xferIn");

                        if ($xferOut != $xferIn) {
                            $classes[] = 'danger';
                        }
                        break;
                }
            }

            // Keeping these functions around for debugging later. For prod, short circuit
            if (!$originals) {
                return implode(' ', array_unique($classes));
            }

            $original = array_get($originals[$centerName], $name);
            $ours = array_get($reportData[$centerName], $name);

            if ($original != $ours) {
                $classes[] = 'warning';
            }

            return implode(' ', array_unique($classes));
        }

        function getValue($name, $centerName, $reportData, $originals) {
            // Keeping these functions around for debugging later. For prod, short circuit
            if (!$originals) {
                return array_get($reportData[$centerName], $name);
            }

            $original = array_get($originals[$centerName], $name);
            $value = array_get($reportData[$centerName], $name);

            if ($original != $value) {
                $value .= " ({$original})";
            }

            return $value;
        }

        function getOfficialData($teamYear) {
            // Keeping these functions around for debugging later. For prod, short circuit
            $data = file(storage_path() . "/app/team{$teamYear}summary.csv");
            $csv = array_map('str_getcsv', $data);

            array_walk($csv, function(&$a) use ($csv) {
                if (count($a) != count($csv[0])) {
                    $diff = count($csv[0]) - count($a);
                    $a = array_merge($a, array_fill(count($a), $diff, ''));
                }
                $a = array_combine($csv[0], $a);
            });
            // remove column header
            array_shift($csv);

            $dataArr = [];
            foreach ($csv as $row) {
                if (!$row['centerName']) {
                    continue;
                }

                $centerName = getName($row['centerName']);
                $dataArr[$centerName] = $row;
            }

            return $dataArr;
        }

        function getName($name) {
            // Keeping these functions around for debugging later. For prod, short circuit
            switch($name) {
                case 'ATLANTA':
                    return 'Atlanta';
                case 'BOSTON':
                    return 'Boston';
                case 'CHICAGO':
                    return 'Chicago';
                case 'Dallas':
                    return 'Dallas';
                case 'Denver':
                    return 'Denver';
                case 'DETROIT':
                    return 'Detroit';
                case 'FLORIDA':
                    return 'Florida';
                case 'Houston':
                    return 'Houston';
                case 'Los Angeles':
                    return 'Los Angeles';
                case 'Mexico':
                    return 'Mexico';
                case 'MINN/ST PAUL':
                    return 'MSP';
                case 'MONTREAL':
                    return 'Montreal';
                case 'NEW JERSEY':
                    return 'New Jersey';
                case 'NEW YORK':
                    return 'New York';
                case 'Orange County':
                    return 'Orange County';
                case 'PHILADELPHIA':
                    return 'Philadelphia';
                case 'Phoenix':
                    return 'Phoenix';
                case 'San Diego':
                    return 'San Diego';
                case 'San Francisco':
                    return 'San Francisco';
                case 'San Jose':
                    return 'San Jose';
                case 'Seattle':
                    return 'Seattle';
                case 'TORONTO':
                    return 'Toronto';
                case 'Vancouver':
                    return 'Vancouver';
                case 'WASH DC':
                    return 'Washington, DC';
                case 'North America Totals':
                    return 'totals';
                case 'Registration Fulfillment:':
                    return 'regfulfill';
            }

            return $name;
        }
    }
?>
<br/>
<div class="table-responsive">
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th rowspan="2" class="border-right">Center</th>
            <th colspan="1" class="data-point border-right rotate45">Qtr Promise</th>
            <th colspan="2" class="data-point border-right">Registered</th>
            <th colspan="{{ ($teamYear == 2) ? 3 : 2 }}" class="data-point border-right">Weekend Reg</th>
            <th colspan="4" class="data-point border-right">Applications for Upcoming AND Future Weekends</th>
            <th colspan="4" class="data-point border-right">Applications For Upcoming Weekend</th>
            <th colspan="1" class="data-point border-right">On Team After The Weekend</th>
            <th colspan="1" class="data-point border-right">Xfer Out</th>
            <th colspan="1" class="data-point border-right">Xfer In</th>
            <th colspan="5" class="data-point border-right">Withdraws</th>
            <th colspan="1" class="data-point border-right">Well Being Issue</th>
            <th colspan="1" class="data-point border-right">Conv. To Withdraw</th>
            <th colspan="1" class="data-point border-right">Reregisterd</th>
            <th colspan="1" class="data-point border-right">Currently On Team</th>
            <th colspan="1" class="data-point border-right">TDOs</th>
            <th colspan="1" class="data-point border-right">Completing This Weekend</th>
            <th colspan="1" class="data-point border-right">On Team Next Quarter</th>
            <th colspan="1" class="data-point border-right">Attending This Weekend</th>
        </tr>
        <tr>
            <th class="data-point border-right"></th>
            <th class="data-point">Total</th>
            <th class="data-point border-right">Net</th>
            <th class="data-point">Before</th>
            @if ($teamYear == 2)
            <th class="data-point">During</th>
            @endif
            <th class="data-point border-right">After</th>
            <th class="data-point">Out</th>
            <th class="data-point">In</th>
            <th class="data-point">Appr</th>
            <th class="data-point border-right">WD</th>
            <th class="data-point">Out</th>
            <th class="data-point">In</th>
            <th class="data-point">Appr</th>
            <th class="data-point border-right">WD</th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point">Q1</th>
            <th class="data-point">Q2</th>
            <th class="data-point">Q3</th>
            <th class="data-point">Q4</th>
            <th class="data-point border-right">All</th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
            <th class="data-point border-right"></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($reportData as $centerName => $centerData)
            <tr>
                <td class="{{ getClasses('centerName', $centerName, $reportData, $originals, $teamYear) }}">
                    {{ $centerName }}
                </td>
                @foreach ($cols as $col)
                    <td class="{{ getClasses($col, $centerName, $reportData, $originals, $teamYear) }}">{{ getValue($col, $centerName, $reportData, $originals) }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="border-right">Totals:</th>
            @foreach ($cols as $col)
                <th class="{{ getClasses($col, 'totals', ['totals' => $totals], [], $teamYear) }}">{{ getValue($col, 'totals', ['totals' => $totals], []) }}</th>
            @endforeach
        </tr>
        <tr>
            <th class="border-right">Reg Fulfill:</th>
            <th colspan="{{ ($teamYear == 2) ? 6 : 5 }}" class="data-point border-right"></th>
            <th class="data-point">{{ $regFulfill['appStatus']['appOut'] }}%</th>
            <th class="data-point">{{ $regFulfill['appStatus']['appIn'] }}%</th>
            <th class="data-point">{{ $regFulfill['appStatus']['appr'] }}%</th>
            <th class="data-point border-right">{{ $regFulfill['appStatus']['wd'] }}%</th>
            <th class="data-point">{{ $regFulfill['appStatusNext']['appOut'] }}%</th>
            <th class="data-point">{{ $regFulfill['appStatusNext']['appIn'] }}%</th>
            <th class="data-point">{{ $regFulfill['appStatusNext']['appr'] }}%</th>
            <th class="data-point border-right">{{ $regFulfill['appStatusNext']['wd'] }}%</th>
            <th colspan="3" class="data-point border-right"></th>
            <th colspan="5" class="data-point border-right">{{ $regFulfill['withdraws']['all'] }}%</th>
            <th colspan="8" class="data-point border-right"></th>
        </tr>
    </table>

    <h4>Highlights</h4>
    @if ($teamYear == 1)
    <p><strong>Center</strong> or <strong>On Team Next Quarter</strong> highlighted: Team 1 on Team Next Quarter is 7 or less</p>
    @else
    <p><strong>Center</strong> or <strong>On Team Next Quarter</strong> highlighted: Team 2 on Team Next Quarter is 2 or less</p>
    @endif
    <p><strong>Qtr Promise</strong>: Promise is insufficient for expansion next quarter (Quarter Promise minus Number Completing Next Weekend minus Withdrawn minus Well Being Withdraw is less than 1)</p>
    <p><strong>Withdraws</strong> or <strong>Conv. To Withdraw</strong>: Withdrawn and Conversation To Withdraw greater than zero</p>
    <p><strong>Xfer Out</strong> or <strong>Xfer In</strong>: Transfers do not match</p>
</div>
