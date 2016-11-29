<br/>
<div class="table-responsive">
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th rowspan="2" class="border-right">Center</th>
            <th colspan="1" class="data-point border-right rotate45">Qtr Promise</th>
            <th colspan="2" class="data-point border-right">Registered</th>
            <th colspan="2" class="data-point border-right">Weekend Reg</th>
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
                <td class="data-point border-right">{{ $centerData['qtrPromise'] }}</td>
                <td class="data-point">{{ $centerData['registrations']['total'] }}</td>
                <td class="data-point border-right">{{ $centerData['registrations']['net'] }}</td>
                <td class="data-point">{{ $centerData['wkndReg']['before'] }}</td>
                <td class="data-point border-right">{{ $centerData['wkndReg']['after'] }}</td>
                <td class="data-point">{{ $centerData['appStatus']['appOut'] }}</td>
                <td class="data-point">{{ $centerData['appStatus']['appIn'] }}</td>
                <td class="data-point">{{ $centerData['appStatus']['appr'] }}</td>
                <td class="data-point border-right">{{ $centerData['appStatus']['wd'] }}</td>
                <td class="data-point">{{ $centerData['appStatusNext']['appOut'] }}</td>
                <td class="data-point">{{ $centerData['appStatusNext']['appIn'] }}</td>
                <td class="data-point">{{ $centerData['appStatusNext']['appr'] }}</td>
                <td class="data-point border-right">{{ $centerData['appStatusNext']['wd'] }}</td>
                <td class="data-point border-right">{{ $centerData['onTeamAtWknd'] }}</td>
                <td class="data-point border-right">{{ $centerData['xferOut'] }}</td>
                <td class="data-point border-right">{{ $centerData['xferIn'] }}</td>
                <td class="data-point">{{ $centerData['withdraws']['q1'] }}</td>
                <td class="data-point">{{ $centerData['withdraws']['q2'] }}</td>
                <td class="data-point">{{ $centerData['withdraws']['q3'] }}</td>
                <td class="data-point">{{ $centerData['withdraws']['q4'] }}</td>
                <td class="data-point border-right">{{ $centerData['withdraws']['all'] }}</td>
                <td class="data-point border-right">{{ $centerData['wbo'] }}</td>
                <td class="data-point border-right">{{ $centerData['ctw'] }}</td>
                <td class="data-point border-right">{{ $centerData['rereg'] }}</td>
                <td class="data-point border-right">{{ $centerData['currentOnTeam'] }}</td>
                <td class="data-point border-right">{{ $centerData['tdo'] }}</td>
                <td class="data-point border-right">{{ $centerData['completing'] }}</td>
                <td class="data-point border-right">{{ $centerData['onTeamNextQtr'] }}</td>
                <td class="data-point border-right">{{ $centerData['attendingWeekend'] }}</td>
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="border-right">Totals:</th>
            <th class="data-point border-right">{{ $totals['qtrPromise'] }}</th>
            <th class="data-point">{{ $totals['registrations']['total'] }}</th>
            <th class="data-point border-right">{{ $totals['registrations']['net'] }}</th>
            <th class="data-point">{{ $totals['wkndReg']['before'] }}</th>
            <th class="data-point border-right">{{ $totals['wkndReg']['after'] }}</th>
            <th class="data-point">{{ $totals['appStatus']['appOut'] }}</th>
            <th class="data-point">{{ $totals['appStatus']['appIn'] }}</th>
            <th class="data-point">{{ $totals['appStatus']['appr'] }}</th>
            <th class="data-point border-right">{{ $totals['appStatus']['wd'] }}</th>
            <th class="data-point">{{ $totals['appStatusNext']['appOut'] }}</th>
            <th class="data-point">{{ $totals['appStatusNext']['appIn'] }}</th>
            <th class="data-point">{{ $totals['appStatusNext']['appr'] }}</th>
            <th class="data-point border-right">{{ $totals['appStatusNext']['wd'] }}</th>
            <th class="data-point border-right">{{ $totals['onTeamAtWknd'] }}</th>
            <th class="data-point border-right">{{ $totals['xferOut'] }}</th>
            <th class="data-point border-right">{{ $totals['xferIn'] }}</th>
            <th class="data-point">{{ $totals['withdraws']['q1'] }}</th>
            <th class="data-point">{{ $totals['withdraws']['q2'] }}</th>
            <th class="data-point">{{ $totals['withdraws']['q3'] }}</th>
            <th class="data-point">{{ $totals['withdraws']['q4'] }}</th>
            <th class="data-point border-right">{{ $totals['withdraws']['all'] }}</th>
            <th class="data-point border-right">{{ $totals['wbo'] }}</th>
            <th class="data-point border-right">{{ $totals['ctw'] }}</th>
            <th class="data-point border-right">{{ $totals['rereg'] }}</th>
            <th class="data-point border-right">{{ $totals['currentOnTeam'] }}</th>
            <th class="data-point border-right">{{ $totals['tdo'] }}</th>
            <th class="data-point border-right">{{ $totals['completing'] }}</th>
            <th class="data-point border-right">{{ $totals['onTeamNextQtr'] }}</th>
            <th class="data-point border-right">{{ $totals['attendingWeekend'] }}</th>
        </tr>
        <tr>
            <th class="border-right">Reg Fulfill:</th>
            <th colspan="5" class="data-point border-right"></th>
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
