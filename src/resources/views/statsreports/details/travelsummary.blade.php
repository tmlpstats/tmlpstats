<div class="table-responsive">
    <br/>
    <h4>Travel &amp Rooming Summary</h4>
    @include('reports.summaryboxes', compact('boxes'))

    <br/>
    <h4>Team Members</h4>
    <table class="table table-condensed table-striped table-hover want-datatable">
        <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name Initial</th>
            <th class="data-point">Quarter</th>
            <th class="data-point">Travel</th>
            <th class="data-point">Room</th>
            <th>Comment</th>
        </tr>
        </thead>
        <tbody>
        @foreach (['team1', 'team2'] as $group)
            @if (isset($teamMembers[$group]))
                @foreach ($teamMembers[$group] as $quarterNumber => $quarterClass)
                    @foreach ($quarterClass as $memberData)
                        <tr>
                            <td>{{ $memberData->firstName }}</td>
                            <td>{{ $memberData->lastName }}</td>
                            <td class="data-point">{{ $group == 'team1' ? 'T1' : 'T2' }}{{ $quarterNumber }}</td>
                            <td class="data-point">
                                @if ($memberData->travel)
                                    <span class="glyphicon glyphicon-ok"></span>
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($memberData->room)
                                    <span class="glyphicon glyphicon-ok"></span>
                                @endif
                            </td>
                            <td>
                                {{ $memberData->comment }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endif
        @endforeach
        </tbody>
    </table>

    <br/>
    <h4>Incoming</h4>
    <table class="table table-condensed table-striped table-hover want-datatable">
        <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name Initial</th>
            <th>Application Status</th>
            <th class="data-point">Travel</th>
            <th class="data-point">Room</th>
            <th>Comment</th>
        </tr>
        </thead>
        <tbody>
        @foreach (['approved', 'waiting', 'out', 'notSent'] as $group)
            @if (isset($applications[$group]))
                @foreach ($applications[$group] as $registrationData)
                    <tr>
                        <td>{{ $registrationData->firstName }}</td>
                        <td>{{ $registrationData->lastName }}</td>
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
                        <td class="data-point">
                            @if ($registrationData->travel)
                                <span class="glyphicon glyphicon-ok"></span>
                            @endif
                        </td>
                        <td class="data-point">
                            @if ($registrationData->room)
                                <span class="glyphicon glyphicon-ok"></span>
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

