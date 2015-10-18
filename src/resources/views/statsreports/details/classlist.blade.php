<div class="table-responsive">
    <h3>Team Members</h3>
    @foreach ($teamMembers as $groupName => $group)
        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                    <th colspan="14">{{ ucwords($groupName) }}</th>
                </tr>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Quarter</th>
                    <th>GITW</th>
                    <th>TDO</th>
                    <th>WD</th>
                    <th>Accountability</th>
                    <th>Comment</th>
                    <th>Travel</th>
                    <th>Room</th>
                    <th>Special</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group as $memberData)
                    <tr>
                        <td>{{ $memberData->firstName }}</td>
                        <td>{{ $memberData->lastName }}</td>
                        <td style="text-align: center">Q{{ $memberData->teamMember->quarterNumber }}</td>
                        <td style="text-align: center">{{ $memberData->gitw ? 'E' : 'I' }}</td>
                        <td style="text-align: center">{{ $memberData->tdo ? 'Y' : 'N' }}</td>
                        @if ($memberData->withdrawCode)
                            <td title="{{ $memberData->withdrawCode->display }}">{{ $memberData->withdrawCode->code }}</td>
                        @else
                            <td></td>
                        @endif
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
                        <td>{{ $memberData->comment }}</td>
                        <td style="text-align: center">{{ $memberData->travel ? 'Yes' : '' }}</td>
                        <td style="text-align: center">{{ $memberData->room ? 'Yes' : '' }}</td>
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
            </tbody>
        </table>
    @endforeach
</div>
