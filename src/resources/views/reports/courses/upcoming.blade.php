<table class="table table-condensed table-striped table-hover want-datatable" style="width: 100%;">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th colspan="3" class="data-point border-left">Quarter Starting</th>
            <th colspan="4" class="data-point border-left">Current</th>
            @unless (isset($excludeGuestGame) && $excludeGuestGame)
                <th colspan="3" class="data-point border-left">Guest Game</th>
            @endunless
        </tr>
        <tr>
            <th>Date</th>
            <th class="data-point">Location</th>
            <th class="data-point border-left">Total Ever Registered</th>
            <th class="data-point">Standard Starts</th>
            <th class="data-point">Transferred from Previous</th>
            <th class="data-point border-left">Total Ever Registered</th>
            <th class="data-point">Standard Starts</th>
            <th class="data-point">Transferred from Previous</th>
            <th class="data-point border-left">Reg Fulfillment</th>
            @unless (isset($excludeGuestGame) && $excludeGuestGame)
                <th class="data-point border-left">Promised</th>
                <th class="data-point">Invited</th>
                <th class="data-point">Confirmed</th>
            @endunless
        </tr>
    </thead>
    <tbody>
    @foreach ($coursesData as $courseData)
        <tr>
            <td data-order="{{ $courseData['startDate']->getTimestamp() }}">@date($courseData['startDate'])</td>
            <td class="data-point">
                @if (isset($statsReports[$courseData['centerName']]))
                    @statsReportLink($statsReports[$courseData['centerName']])
                        {{ $courseData['location'] != $courseData['centerName'] ? "{$courseData['centerName']} ({$courseData['location']})" : $courseData['centerName'] }}
                    @endStatsReportLink
                @else
                    {{ $courseData['location'] != $courseData['centerName'] ? "{$courseData['centerName']} ({$courseData['location']})" : $courseData['centerName'] }}
                @endif
            </td>
            <td class="data-point border-left">{{ $courseData['quarterStartTer'] }}</td>
            <td class="data-point">{{ $courseData['quarterStartStandardStarts'] }}</td>
            <td class="data-point">{{ $courseData['quarterStartXfer'] }}</td>
            <td class="data-point border-left">{{ $courseData['currentTer'] }}</td>
            <td class="data-point">{{ $courseData['currentStandardStarts'] }}</td>
            <td class="data-point">{{ $courseData['currentXfer'] }}</td>
            <td class="data-point border-left">{{ $courseData['completionStats']['registrationFulfillment'] }}%</td>
            @unless (isset($excludeGuestGame) && $excludeGuestGame)
                <td class="data-point border-left">{{ $courseData['guestsPromised'] or '-' }}</td>
                <td class="data-point">{{ $courseData['guestsInvited'] or '-' }}</td>
                <td class="data-point">{{ $courseData['guestsConfirmed'] or '-' }}</td>
            @endunless
        </tr>
    @endforeach
    </tbody>
 </table>
