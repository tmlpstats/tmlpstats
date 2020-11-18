<div class="table-responsive">

    <br/>
    <h4>On Team After Next Weekend</h4>
    @include('reports.summaryboxes', compact('boxes'))

    <br/>
    <h4>Completing Team Members</h4>
    <table class="table table-condensed table-striped table-hover want-datatable">
        <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name Initial</th>
            <th class="data-point">Team Year</th>
        </tr>
        </thead>
        <tbody>
        @foreach (['team1', 'team2'] as $group)
            @if (isset($teamMembers[$group]['Q4']))
                @foreach ($teamMembers[$group]['Q4'] as $memberData)
                    <tr>
                        <td>{{ $memberData->firstName }}</td>
                        <td>{{ $memberData->lastName }}</td>
                        <td class="data-point">{{ $group == 'team1' ? 'Team 1' : 'Team 2' }}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach
        </tbody>
    </table>

    @if (isset($applications['approved']))
    <br/>
    <h4>Approved Incoming</h4>
    <table class="table table-condensed table-striped table-hover want-datatable">
        <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name Initial</th>
            <th class="data-point">Team Year</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($applications['approved'] as $registrationData)
                <tr>
                    <td>{{ $registrationData->firstName }}</td>
                    <td>{{ $registrationData->lastName }}</td>
                    <td class="data-point">Team {{ $registrationData->teamYear }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <br/>
    <h4>Outstanding Applications</h4>
    <table class="table table-condensed table-striped table-hover want-datatable">
        <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name Initial</th>
            <th class="data-point">Team Year</th>
            <th>Application Status</th>
            <th>Comment</th>
        </tr>
        </thead>
        <tbody>
        @foreach (['waiting', 'out', 'notSent'] as $group)
            @if (isset($applications[$group]))
                @foreach ($applications[$group] as $registrationData)
                    <tr>
                        <td>{{ $registrationData->firstName }}</td>
                        <td>{{ $registrationData->lastName }}</td>
                        <td class="data-point">Team {{ $registrationData->teamYear }}</td>
                        <td>
                            @if ($group == 'out')
                                Application Out
                            @elseif ($group == 'waiting')
                                Awaiting Interview
                            @elseif ($group == 'approved')
                                Approved
                            @else
                                Application Not Sent
                            @endif
                        </td>
                        <td>
                            {{ $registrationData->comment }}
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
        </tbody>
    </table>
</div>

