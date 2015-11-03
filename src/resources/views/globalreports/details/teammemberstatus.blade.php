<div class="table-responsive">
    @foreach (['xferIn', 'xferOut', 'ctw', 'withdrawn'] as $group)
        @if ($reportData[$group])
            <br/>
            <h4>
                @if ($group == 'xferIn')
                    Transfers In
                @elseif ($group == 'xferOut')
                    Transfers Out
                @elseif ($group == 'ctw')
                    Conversations to Withdraw
                @else
                    Withdrawn
                @endif
            </h4>
            <table class="table table-condensed table-striped table-hover teamMemberStatusTable">
                <thead>
                <tr>
                    <th>Center</th>
                    <th>First</th>
                    <th>Last</th>
                    <th style="text-align: center">Quarter</th>
                    @if ($group == 'withdrawn')
                        <th>Reason</th>
                        <th>Withdraw</th>
                    @endif
                    <th>Comments</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reportData[$group] as $memberData)
                    <tr>
                        <td>{{ $memberData->center->name }}</td>
                        <td>{{ $memberData->firstName }}</td>
                        <td>{{ $memberData->lastName }}</td>
                        <td style="text-align: center">T{{ $memberData->teamMember->teamYear }}
                            Q{{ $memberData->teamMember->quarterNumber }}</td>
                        @if ($group == 'withdrawn')
                            @if ($memberData->withdrawCode)
                                <td title="{{ $memberData->withdrawCode->code }}">
                                    {{ $memberData->withdrawCode->display }}
                                </td>
                                <td title="{{ $memberData->withdrawCode->code }}">
                                    {{ $memberData->wdDate ? $memberData->wdDate->format('n/j/y') : '' }}
                                </td>
                            @endif
                        @endif
                        <td>{{ is_numeric($memberData->comment) ? TmlpStats\Util::getExcelDate($memberData->comment)->format('F') : $memberData->comment }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</div>

<script src="{{ asset('/js/query.dataTables.min.js') }}"></script>
<script src="{{ asset('/js/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('table.teamMemberStatusTable').dataTable({
            "paging": false,
            "searching": false
        });
    });
</script>
