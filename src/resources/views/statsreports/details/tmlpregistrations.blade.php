<div class="table-responsive">
    @foreach (['team1', 'team2', 'withdrawn'] as $group)
        @if (isset($reportData[$group]))
            <br/>
            <h4>{{ ucwords($group) }}</h4>
            @foreach ($reportData[$group] as $quarterName => $quarterRegistrations)
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                    @if ($group != 'withdrawn')
                        <tr>
                            <th colspan="14">Starting {{ ucwords($quarterName) }} Quarter</th>
                        </tr>
                    @endif
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Reg Date</th>
                        <th>App Out Date</th>
                        <th>App In Date</th>
                        <th>Approve Date</th>
                        @if ($group == 'withdrawn')
                            <th>Reason</th>
                            <th>Withdraw Date</th>
                        @elseif ($quarterName == 'next')
                            <th>Travel</th>
                            <th>Room</th>
                        @endif
                        <th>Comments</th>
                        <th>Committed Team Member</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($quarterRegistrations as $registrationData)
                        <tr>
                            <td>{{ $registrationData->firstName }}</td>
                            <td>{{ $registrationData->lastName }}</td>
                            <td>{{ $registrationData->regDate ? $registrationData->regDate->format('n/j/y') : '' }}</td>
                            <td>{{ $registrationData->appOutDate ? $registrationData->appOutDate->format('n/j/y') : '' }}</td>
                            <td>{{ $registrationData->appInDate ? $registrationData->appInDate->format('n/j/y') : '' }}</td>
                            <td>{{ $registrationData->apprDate ? $registrationData->apprDate->format('n/j/y') : '' }}</td>
                            @if ($group == 'withdrawn')
                                @if ($registrationData->withdrawCode)
                                    <td title="{{ $registrationData->withdrawCode->display }}">
                                        {{ $registrationData->withdrawCode->code }}
                                    </td>
                                    <td title="{{ $registrationData->withdrawCode->display }}">
                                        {{ $registrationData->wdDate ? $registrationData->wdDate->format('n/j/y') : '' }}
                                    </td>
                                @else
                                    <td></td>
                                    <td></td>
                                @endif
                            @elseif ($quarterName == 'next')
                                <td>
                                    @if ($registrationData->travel)
                                        <span class="glyphicon glyphicon-ok"></span>
                                    @endif
                                </td>
                                <td>
                                    @if ($registrationData->room)
                                        <span class="glyphicon glyphicon-ok"></span>
                                    @endif
                                </td>
                            @endif
                            <td>{{ is_numeric($registrationData->comment) ? TmlpStats\Util::getExcelDate($registrationData->comment)->format('F') : $registrationData->comment }}</td>
                            <td>{{ $registrationData->committedTeamMember ? $registrationData->committedTeamMember->firstName . ' ' . $registrationData->committedTeamMember->lastName : '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endforeach
</div>
