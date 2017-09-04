<div>
<?php
$sections = [
    'Out of Compliance' => $reportData,
    "Only one more withdraw and they're out of compliance" => $almostOutOfCompliance,
];
?>
<p>In compliance means:</p>
<ul>
<li>Less than 5% of Team 1 or Team 2 have withdrawn if team has 20 or more participants</li>
<li>No more than 1 Team 1 or Team 2 have withdrawn if team has less than 20 participants</li>
</ul>

@foreach ($sections as $title => $sectionData)
<h3>{{ $title }}</h3>

@foreach (['team1', 'team2'] as $team)

<table class="table want-datatable">
<thead>
<tr>
    <th>{{ ucfirst($team) }}</th>
    <th class="data-point">Total Team</th>
    <th class="data-point">Withdraws</th>
    <th class="data-point">%</th>
    <th>Classroom Leader</th>
</tr>
</thead>

<tbody>
@foreach ($sectionData as $centerName => $data)
@if (isset($data[$team]) && $data[$team]['withdrawCount'] > 0)
<tr>
    <td>{{ $centerName }}</td>
    <td class="data-point">{{ $data[$team]['totalCount'] }}</td>
    <td class="data-point">{{ $data[$team]['withdrawCount'] }}</td>
    <td class="data-point">{{ round($data[$team]['percent'], 1) }}%</td>
    <td>{{ $data['classroomLeader'] }}</td>
</tr>
@endif
@endforeach
</tbody>
</table>
<br>
@endforeach
@endforeach

</div>
