<br/>
<table class="table table-condensed table-striped table-hover">
<thead>
    <th>Center Name</th>
    <th>Program Managers</th>
    <th>Classroom Leaders</th>
    <th>General Session</th>
    <th>Currently on Team 1</th>
    <th>Team 1 Incoming</th>
    <th>Team 1 Completing</th>
    <th>Currently on Team 2</th>
    <th>Team 2 Incoming</th>
    <th>Team 2 Completing</th>
    <th>All T2</th>
    <th>Team 2 &amp; PM/CL</th>
    <th>Team 2 Registration Event</th>
</thead>
<tbody>
@foreach ($reportData as $centerName => $centerData)
<tr>
    <td>{{ $centerName }}</td>
    <td>{{ $centerData['pmAttending'] }}</td>
    <td>{{ $centerData['clAttending'] }}</td>
    <td>{{ $centerData['generalSession'] }}</td>
    <td>{{ $centerData['team1Current'] }}</td>
    <td>{{ $centerData['team1Incoming'] }}</td>
    <td>{{ $centerData['team1Completing'] }}</td>
    <td>{{ $centerData['team2Current'] }}</td>
    <td>{{ $centerData['team2Incoming'] }}</td>
    <td>{{ $centerData['team2Completing'] }}</td>
    <td>{{ $centerData['team2RoomSatPm'] }}</td>
    <td>{{ $centerData['team2Room'] }}</td>
    <td>{{ $centerData['team2RegistrationEvent'] }}</td>
</tr>
@endforeach
<tr>
    <th>Totals</th>
    <th>{{ $totals['pmAttending'] }}</th>
    <th>{{ $totals['clAttending'] }}</th>
    <th>{{ $totals['generalSession'] }}</th>
    <th>{{ $totals['team1Current'] }}</th>
    <th>{{ $totals['team1Incoming'] }}</th>
    <th>{{ $totals['team1Completing'] }}</th>
    <th>{{ $totals['team2Current'] }}</th>
    <th>{{ $totals['team2Incoming'] }}</th>
    <th>{{ $totals['team2Completing'] }}</th>
    <th>{{ $totals['team2RoomSatPm'] }}</th>
    <th>{{ $totals['team2Room'] }}</th>
    <th>{{ $totals['team2RegistrationEvent'] }}</th>
</tr>
<tr>
    <td></td>
    <td></td>
    <td></td>
    <td>PM, CRL,<br/>
        All T1,<br/>
        All T2</td>
    <td>Total includes <br/>
        "Completing T1s"</td>
    <td></td>
    <td></td>
    <td>Total includes <br/>
        "Completing T2s"</td>
    <td></td>
    <td></td>
    <td>PM, CRL,<br/>
        All T2</td>
    <td>PM,<br/>
        Completing T1,<br/>
        All T2</td>
</tr>
</tbody>
</table>
