<div class="table-responsive">
    @foreach (['team1', 'team2', 'withdrawn'] as $group)
        @if (isset($reportData[$group]))
            <br/>
            <h4>{{ ucwords($group) }}</h4>
            <table class="table table-condensed table-striped table-hover">
                <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th style="text-align: center">Quarter</th>
                    <th>Accountability</th>
                    @if ($group != 'withdrawn')
                        <th style="text-align: center">GITW</th>
                        <th style="text-align: center">TDO</th>
                        <th style="text-align: center">Travel</th>
                        <th style="text-align: center">Room</th>
                    @else
                        <th>Withdraw</th>
                    @endif
                    <th>Comment</th>
                    <th>Special</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reportData[$group] as $quarterNumber => $quarterClass)
                    @foreach ($quarterClass as $memberData)
                        <tr>
                            <td>{{ $memberData->firstName }}</td>
                            <td>{{ $memberData->lastName }}</td>
                            <td style="text-align: center">{{ $quarterNumber }}</td>
                            <td><?php
                                $accountabilities = $memberData->teamMember->person->accountabilities()->get();

                                if ($accountabilities) {
                                    $accountabilityNames = array();
                                    foreach ($accountabilities as $accountability) {
                                        $accountabilityNames[] = $accountability->display;
                                    }
                                    echo implode(', ', $accountabilityNames);
                                }
                                ?></td>
                            @if ($group != 'withdrawn')
                                <td style="text-align: center">{{ $memberData->gitw ? 'E' : 'I' }}</td>
                                <td style="text-align: center">{{ $memberData->tdo ? 'Y' : 'N' }}</td>
                                <td style="text-align: center">
                                    @if ($memberData->travel)
                                        <span class="glyphicon glyphicon-ok"></span>
                                    @endif
                                </td>
                                <td style="text-align: center">
                                    @if ($memberData->travel)
                                        <span class="glyphicon glyphicon-ok"></span>
                                    @endif
                                </td>
                            @else
                                @if ($memberData->withdrawCode)
                                    <td title="{{ $memberData->withdrawCode->code }}">{{ $memberData->withdrawCode->display }}</td>
                                @else
                                    <td></td>
                                @endif
                            @endif
                            <td>{{ is_numeric($memberData->comment) ? TmlpStats\Util::getExcelDate($memberData->comment)->format('M j, Y') : $memberData->comment }}</td>
                            <td><?php
                                $special = array();
                                if (!$memberData->atWeekend) {
                                    $special[] = "Not at weekend";
                                }
                                if ($memberData->xferOut) {
                                    $special[] = "Xfer Out";
                                }
                                if ($memberData->xferIn) {
                                    $special[] = "Xfer In";
                                }
                                if ($memberData->ctw) {
                                    $special[] = "CTW";
                                }
                                if ($memberData->rereg) {
                                    $special[] = "Re-reg";
                                }
                                if ($memberData->excep) {
                                    $special[] = "Excep";
                                }
                                echo implode(', ', $special);
                                ?></td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</div>
