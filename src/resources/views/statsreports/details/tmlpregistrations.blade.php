<div class="table-responsive">
    @foreach (['team1', 'team2', 'withdrawn'] as $group)
        @if (isset($reportData[$group]) && count($reportData[$group]))
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
                        <th class="data-point">Reg Date</th>
                        <th class="data-point">App Out Date</th>
                        <th class="data-point">App In Date</th>
                        <th class="data-point">Approve Date</th>
                        @if ($group == 'withdrawn')
                            <th class="data-point">Reason</th>
                            <th class="data-point">Withdraw Date</th>
                        @elseif ($quarterName == 'next')
                            <th class="data-point">Travel</th>
                            <th class="data-point">Room</th>
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
                            <td class="data-point">
                                @if ($registrationData->regDate)
                                    @date($registrationData->regDate)
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($registrationData->appOutDate)
                                    @date($registrationData->appOutDate)
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($registrationData->appInDate)
                                    @date($registrationData->appInDate)
                                @endif
                            </td>
                            <td class="data-point">
                                @if ($registrationData->apprDate)
                                    @date($registrationData->apprDate)
                                @endif
                            </td>
                            @if ($group == 'withdrawn')
                                @if ($registrationData->withdrawCode)
                                    <td class="data-point" title="{{ $registrationData->withdrawCode->display }}">
                                        {{ $registrationData->withdrawCode->code }}
                                    </td>
                                    <td class="data-point" title="{{ $registrationData->withdrawCode->display }}">
                                        @if ($registrationData->wdDate)
                                            @date($registrationData->wdDate)
                                        @endif
                                    </td>
                                @else
                                    <td></td>
                                    <td></td>
                                @endif
                            @elseif ($quarterName == 'next')
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
