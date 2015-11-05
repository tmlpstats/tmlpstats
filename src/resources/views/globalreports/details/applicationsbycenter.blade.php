<div class="table-responsive">
    @foreach ($reportData as $centerName => $centerData)
        <br/>
        <h3>{{ $centerName }}</h3>
        @foreach (['team1', 'team2', 'withdrawn'] as $group)
            @if (isset($centerData[$group]) && count($centerData[$group]))
                <h4>{{ ucwords($group) }}</h4>
                <table class="table table-condensed table-striped table-hover applicationTable">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Reg Quarter</th>
                        @if ($group != 'withdrawn')
                            <th>Status</th>
                        @else
                            <th>Reason</th>
                        @endif
                        <th>Incoming Quarter</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($centerData[$group] as $quarterName => $quarterRegistrations)
                        @foreach ($quarterRegistrations as $registrationData)
                            <tr>
                                <td>{{ $registrationData->firstName }} {{ $registrationData->lastName }}</td>
                                <td>
                                    @if ($registrationData->regDate)
                                        @if ($registrationData->regDate->gt($registrationData->statsReport->quarter->startWeekendDate))
                                            Current
                                        @else
                                            Prior
                                        @endif
                                    @endif
                                </td>

                                @if ($group != 'withdrawn')
                                    <td>
                                        @if ($registrationData->apprDate)
                                            Approved
                                        @elseif ($registrationData->appInDate)
                                            App In
                                        @elseif ($registrationData->appOutDate)
                                            App Out
                                        @else
                                            App not sent
                                        @endif
                                    </td>
                                @else
                                    @if ($registrationData->withdrawCode)
                                        <td title="{{ $registrationData->withdrawCode->code }}">
                                            {{ $registrationData->withdrawCode->display }}
                                        </td>
                                    @else
                                        <td></td>
                                    @endif
                                @endif
                                <td>{{ ucwords($quarterName) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
        <br />
    @endforeach
</div>
