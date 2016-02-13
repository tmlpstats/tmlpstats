<div class="table-responsive">
    @foreach ($types as $group)
        <br/>
        <h4>
            @if ($group == 'xferIn')
                Transfers In
            @elseif ($group == 'xferOut')
                Transfers Out
            @elseif ($group == 'ctw')
                Conversations to Withdraw
            @elseif ($group == 'withdrawn')
                Withdrawn
            @else
                Team 2 Potentials
            @endif
        </h4>
        @if (!isset($reportData[$group]) || !$reportData[$group])
            <p>None found</p>
        @else
            <table class="table table-condensed table-striped table-hover {{ $group }}TeamMemberStatusTable want-datatable">
                <thead>
                <tr>
                    <th>Center</th>
                    <th>First</th>
                    <th>Last</th>
                    <th class="data-point">Quarter</th>
                    @if ($group == 'withdrawn')
                        <th>Reason</th>
                    @elseif ($group == 't2Potential')
                        <th class="data-point">Registered</th>
                        <th class="data-point">Approved</th>
                    @endif
                    <th>Comments</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reportData[$group] as $memberData)
                    <tr>
                        <td>
                            @statsReportLink($memberData->statsReport)
                                {{ $memberData->center->name }}
                            @endStatsReportLink
                        </td>
                        <td>{{ $memberData->firstName }}</td>
                        <td>{{ $memberData->lastName }}</td>
                        <td class="data-point">
                            T{{ $memberData->teamMember->teamYear }}
                            Q{{ $memberData->teamMember->quarterNumber }}
                        </td>
                        @if ($group == 'withdrawn')
                            @if ($memberData->withdrawCode)
                                <td title="{{ $memberData->withdrawCode->code }}">
                                    {{ $memberData->withdrawCode->display }}
                                </td>
                            @endif
                        @elseif ($group == 't2Potential')
                            <td class="data-point">
                                @if (isset($registrations[$memberData->teamMember->personId]))
                                    <span class="glyphicon glyphicon-ok"></span>
                                @endif
                            </td>
                            <td class="data-point">
                                @if (isset($registrations[$memberData->teamMember->personId]) && $registrations[$memberData->teamMember->personId]->apprDate)
                                    <span class="glyphicon glyphicon-ok"></span>
                                @endif
                            </td>
                        @endif
                        <td>{{ is_numeric($memberData->comment) ? TmlpStats\Util::getExcelDate($memberData->comment)->format('F') : $memberData->comment }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</div>
