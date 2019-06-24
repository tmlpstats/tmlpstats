<table class="table table-condensed table-striped table-hover want-datatable" style="width: 100%;">
    <thead>
    <tr>
        <th>Date</th>
        <th>Location</th>
        <th>Course</th>
        <th class="data-point border-left">Standard Starts</th>
        <th class="data-point border-left">Promised</th>
        <th class="data-point">Invited</th>
        <th class="data-point">Confirmed</th>
        <th class="data-point">Attended</th>
        <th class="data-point border-left">Games Effectiveness</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($coursesData as $courseData)
        <tr>
            <td data-order="{{ $courseData['startDate']->getTimestamp() }}">@date($courseData['startDate'])</td>
            <td>
                @if (isset($statsReports))
                    @statsReportLink($statsReports[$courseData['centerName']])
                        {{ $courseData['location'] != $courseData['centerName'] ? "{$courseData['centerName']} ({$courseData['location']})" : $courseData['centerName'] }}
                    @endStatsReportLink
                @else
                    {{ $courseData['location'] != $courseData['centerName'] ? "{$courseData['centerName']} ({$courseData['location']})" : $courseData['centerName'] }}
                @endif
            </td>
            <td class="data-point">{{ $courseData['type'] }}</td>
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
