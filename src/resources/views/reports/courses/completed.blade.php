<table class="table table-condensed table-striped table-hover">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th colspan="3" class="data-point border-left">
                Starting
            </th>
            <th colspan="3" class="data-point border-left">
                Current
            </th>
            <th colspan="5" class="data-point border-left">
                Completion
            </th>
            @unless (isset($excludeGuestGame) && $excludeGuestGame)
                <th colspan="5" class="data-point border-left">
                    Guest Game
                </th>
            @endunless
        </tr>
        <tr>
            <th>&nbsp;</th>
            <th>Location</th>
            <th class="data-point">Date</th>
            <th class="data-point border-left" title="Total Ever Registered">TER</th>
            <th class="data-point" title="Standard Starts">SS</th>
            <th class="data-point" title="Transferred from previous course">Xfer</th>
            <th class="data-point border-left" title="Total Ever Registered">TER</th>
            <th class="data-point" title=" title="Standard Starts">SS</th>
            <th class="data-point" title="Transferred from previous course">Xfer</th>
            <th class="data-point border-left" title="Standard Starts that completed course">SS Completed</th>
            <th class="data-point">Potentials</th>
            <th class="data-point">Registrations</th>
            <th class="data-point border-left">Reg Fulfillment</th>
            <th class="data-point">Reg Effectiveness</th>
            @unless (isset($excludeGuestGame) && $excludeGuestGame)
                <th class="data-point border-left">Promised</th>
                <th class="data-point">Invited</th>
                <th class="data-point">Confirmed</th>
                <th class="data-point">Attended</th>
                <th class="data-point border-left">Guests Effectiveness</th>
            @endunless
        </tr>
    </thead>
    <tbody>
    @foreach ($coursesData as $courseData)
        <tr>
            <td>{{ $courseData['type'] }}</td>
            <td>
                @statsReportLink($statsReports[$courseData['centerName']])
                    {{ $courseData['location'] != $courseData['centerName'] ? "{$courseData['centerName']} ({$courseData['location']})" : $courseData['centerName'] }}
                @endStatsReportLink
            </td>
            <td class="data-point">@date($courseData['startDate'])</td>
            <td class="data-point border-left">{{ $courseData['quarterStartTer'] }}</td>
            <td class="data-point">{{ $courseData['quarterStartStandardStarts'] }}</td>
            <td class="data-point">{{ $courseData['quarterStartXfer'] }}</td>
            <td class="data-point border-left">{{ $courseData['currentTer'] }}</td>
            <td class="data-point">{{ $courseData['currentStandardStarts'] }}</td>
            <td class="data-point">{{ $courseData['currentXfer'] }}</td>
            <td class="data-point border-left">{{ $courseData['completedStandardStarts'] }}</td>
            <td class="data-point">{{ $courseData['potentials'] }}</td>
            <td class="data-point">{{ $courseData['registrations'] }}</td>
            <td class="data-point border-left">{{ $courseData['completionStats']['registrationFulfillment'] }}%</td>
            <td class="data-point">{{ $courseData['completionStats']['registrationEffectiveness'] }}%</td>
            @unless (isset($excludeGuestGame) && $excludeGuestGame)
                <td class="data-point border-left">{{ isset($courseData['guestsPromised']) ? $courseData['guestsPromised'] : '-' }}</td>
                <td class="data-point">{{ $courseData['guestsInvited'] or '-' }}</td>
                <td class="data-point">{{ $courseData['guestsConfirmed'] or '-' }}</td>
                <td class="data-point">{{ $courseData['guestsAttended'] or '-' }}</td>
                <td class="data-point border-left">{{ isset($courseData['completionStats']['guestsGameEffectiveness']) ? $courseData['completionStats']['guestsGameEffectiveness'] . '%' : '-' }}</td>
            @endunless
        </tr>
    @endforeach
    </tbody>
</table>
