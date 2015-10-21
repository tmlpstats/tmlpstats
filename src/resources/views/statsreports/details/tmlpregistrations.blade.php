<div class="table-responsive">
    @foreach ($tmlpRegistrations as $groupName => $group)
        <?php
            if (!in_array($groupName, ['team1', 'team2', 'future'])) {
                continue;
            }
        ?>
        <table class="table table-condensed table-striped table-hover">
            <thead>
                <tr>
                    <th colspan="14">{{ ucwords($groupName) }}</th>
                </tr>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Reg Date</th>
                    <th>App Out Date</th>
                    <th>App In Date</th>
                    <th>Appr Date</th>
                    <th>WD</th>
                    <th>WD Date</th>
                    <th>Committed Team Member</th>
                    <th>Comments</th>
                    <th>Travel</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group as $registrationData)
                    <tr>
                        <td>{{ $registrationData->firstName }}</td>
                        <td>{{ $registrationData->lastName }}</td>
                        <td>{{ $registrationData->regDate ? $registrationData->regDate->format('n/j/y') : '' }}</td>
                        <td>{{ $registrationData->appOutDate ? $registrationData->appOutDate->format('n/j/y') : '' }}</td>
                        <td>{{ $registrationData->appInDate ? $registrationData->appInDate->format('n/j/y') : '' }}</td>
                        <td>{{ $registrationData->apprDate ? $registrationData->apprDate->format('n/j/y') : '' }}</td>
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
                        <td>{{ $registrationData->committedTeamMember ? $registrationData->committedTeamMember->firstName . ' ' . $registrationData->committedTeamMember->lastName : '' }}</td>
                        <td>{{ is_numeric($registrationData->comment) ? TmlpStats\Util::getExcelDate($registrationData->comment)->format('F') : $registrationData->comment }}</td>
                        <td>{{ $registrationData->travel ? 'Yes' : '' }}</td>
                        <td>{{ $registrationData->room ? 'Yes' : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
