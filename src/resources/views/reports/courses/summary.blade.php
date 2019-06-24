<table class="table table-condensed table-striped table-hover" style="width: 100%;">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th class="data-point border-left">
                Courses
            </th>
            <th colspan="2" class="data-point border-left">
                Starting
            </th>
            <th colspan="3" class="data-point border-left">
                Current
            </th>
            <th colspan="4" class="data-point border-left">
                Completion
            </th>
            <th colspan="5" class="data-point border-left">
                Transforming Lives Game
            </th>
        </tr>
        <tr>
            <th class="data-point">&nbsp;</th>
            <th class="data-point border-left">&nbsp;</th>
            <th class="data-point border-left" title="Total Ever Registered">TER</th>
            <th class="data-point" title="Standard Starts">SS</th>
            <th class="data-point border-left" title="Total Ever Registered">TER</th>
            <th class="data-point" title=" title="Standard Starts">SS</th>
            <th class="data-point border-left">Reg Fulfillment</th>
            <th class="data-point border-left" title="Standard Starts that completed course">SS Completed</th>
            <th class="data-point">Potentials</th>
            <th class="data-point">Registrations</th>
            <th class="data-point border-left">Reg Effectiveness</th>
            <th class="data-point border-left">Promised</th>
            <th class="data-point">Invited</th>
            <th class="data-point">Confirmed</th>
            <th class="data-point">Attended</th>
            <th class="data-point border-left">Games Effectiveness</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($coursesData as $state => $courseData)
        <tr>
            <td class="data-point">{{ ucfirst($state) }} Courses</td>
            <td class="data-point border-left">{{ $courseData['courseCount'] }}</td>
            <td class="data-point border-left">{{ $courseData['quarterStartTer'] }}</td>
            <td class="data-point">{{ $courseData['quarterStartStandardStarts'] }}</td>
            <td class="data-point border-left">{{ $courseData['currentTer'] }}</td>
            <td class="data-point">{{ $courseData['currentStandardStarts'] }}</td>
            <td class="data-point border-left">{{ $courseData['registrationFulfillment'] }}%</td>
            <td class="data-point border-left">{{ $state == 'open' ? '-' : $courseData['completedStandardStarts'] }}</td>
            <td class="data-point">{{ $state == 'open' ? '-' : $courseData['potentials'] }}</td>
            <td class="data-point">{{ $state == 'open' ? '-' : $courseData['registrations'] }}</td>
            <td class="data-point border-left">{{ $state == 'open' ? '-' : $courseData['registrationEffectiveness'] . '%' }}</td>
            <td class="data-point border-left">{{ $courseData['guestsPromised'] }}</td>
            <td class="data-point">{{ $courseData['guestsInvited'] }}</td>
            <td class="data-point">{{ $courseData['guestsConfirmed'] }}</td>
            <td class="data-point">{{ $courseData['guestsAttended'] }}</td>
            <td class="data-point border-left">{{ $courseData['guestsGameEffectiveness'] }}%</td>
        </tr>
    @endforeach
    </tbody>
</table>
