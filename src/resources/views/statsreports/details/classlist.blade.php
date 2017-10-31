@inject('context', 'TmlpStats\Api\Context')
<div class="table-responsive">
    @foreach (['team1', 'team2', 'withdrawn'] as $group)
        @if (isset($reportData[$group]))
            <br/>
            <h4>{{ ucwords($group) }}</h4>
            <table class="table table-condensed table-striped table-hover classListTable want-datatable">
                <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th class="data-point">Quarter</th>
                    <th>Accountability</th>
                    @if ($group != 'withdrawn')
                        <th class="data-point">GITW</th>
                        <th class="data-point">TDO</th>
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
                            <td class="data-point">
                                @if ($group == 'withdrawn')
                                    T{{ $memberData->teamYear }}
                                @endif
                                {{ $quarterNumber }}
                            </td>
                            <td><?php
                                $accountabilities = $memberData->teamMember->getAccountabilities($context->getReportingDate()->setTime(15,0,0));
                                if ($accountabilities) {
                                    $accountabilityNames = array();
                                    foreach ($accountabilities as $accountability) {
                                        $accountabilityNames[] = $accountability->display;
                                    }
                                    echo implode(', ', $accountabilityNames);
                                }
                                ?></td>
                            @if ($group != 'withdrawn')
                                <td class="data-point">
                                    @if ($memberData->isActiveMember())
                                        {{ $memberData->gitw ? 'E' : 'I' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="data-point">
                                    @if ($memberData->isActiveMember())
                                        @if ($memberData->tdo > 1)
                                            <strong>{{ $memberData->tdo }}</strong>
                                        @else
                                            {{ $memberData->tdo }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            @else
                                @if ($memberData->withdrawCode)
                                    <td title="{{ $memberData->withdrawCode->code }}">{{ $memberData->withdrawCode->display }}</td>
                                @else
                                    <td></td>
                                @endif
                            @endif
                            <td>{{ is_numeric($memberData->comment) ? TmlpStats\Util::getExcelDate($memberData->comment)->format(TmlpStats\Util::getLocaleDateFormat()) : $memberData->comment }}</td>
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
                                if ($memberData->wbo) {
                                    $special[] = "WBI";
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
