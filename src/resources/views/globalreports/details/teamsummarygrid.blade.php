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

    function getClasses ($name, $centerName, $reportData, $originals) {
        $classes = '';
        switch ($name) {
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
                $classes = 'data-point';
            default:
                $classes = 'data-point border-right';
        }

        // Keeping these functions around for debugging later. For prod, short circuit
        if (!$originals) {
            return $classes;
        }

        $original = array_get($originals[$centerName], $name);
        $ours = array_get($reportData[$centerName], $name);

        if ($original != $ours) {
            $classes .= ' warning';
        }

        return $classes;
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
            <th colspan="1" class="data-point border-right">On Team At Weekend</th>
            <th colspan="1" class="data-point border-right">Xfer Out</th>
            <th colspan="1" class="data-point border-right">Xfer In</th>
            <th colspan="5" class="data-point border-right">Withdraws</th>
            <th colspan="1" class="data-point border-right">Well Being Out</th>
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
                <td class="border-right">
                    {{ $centerName }}
                </td>
                @foreach ($cols as $col)
                    <td class="{{ getClasses($col, $centerName, $reportData, $originals) }}">{{ getValue($col, $centerName, $reportData, $originals) }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="border-right">Totals:</th>
            @foreach ($cols as $col)
                <th class="{{ getClasses($col, $centerName, ['totals' => $totals], []) }}">{{ getValue($col, 'totals', ['totals' => $totals], []) }}</th>
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
</div>
