<table class="table table-condensed table-striped table-hover">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th>Location</th>
        <th>Date</th>
        <th class="data-point border-left">Standard Starts</th>
        <th class="data-point border-left">Promised</th>
        <th class="data-point">Invited</th>
        <th class="data-point">Confirmed</th>
        <th class="data-point">Attended</th>
        <th class="data-point border-left">Guests Effectiveness</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($coursesData as $courseData)
        <tr>
            <td>{{ $courseData['type'] }}</td>
            <td>{{ $courseData['location'] != $courseData['centerName'] ? "{$courseData['centerName']} ({$courseData['location']})" : $courseData['centerName'] }}</td>
            <td class="data-point">@date($courseData['startDate'])</td>
            <td class="data-point border-left">{{ $courseData['currentStandardStarts'] }}</td>
            <td class="data-point border-left">{{ $courseData['guestsPromised'] or '-' }}</td>
            <td class="data-point">{{ $courseData['guestsInvited'] or '-' }}</td>
            <td class="data-point">{{ $courseData['guestsConfirmed'] or '-' }}</td>
            <td class="data-point">{{ $courseData['guestsAttended'] or '-' }}</td>
            <td class="data-point border-left">{{ isset($courseData['completionStats']['guestsGameEffectiveness']) ? $courseData['completionStats']['guestsGameEffectiveness'] . '%' : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
 </table>
