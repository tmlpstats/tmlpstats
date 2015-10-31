<div class="table-responsive">
    @foreach (['notSent', 'out', 'waiting', 'approved', 'withdrawn'] as $group)
        @if ($reportData[$group])
            <br/>
            <h4>
                @if ($group == 'withdrawn')
                    Withdrawn
                @elseif ($group == 'out')
                    Application Out
                @elseif ($group == 'waiting')
                    Awaiting Interview
                @elseif ($group == 'approved')
                    Approved
                @else
                    Application Not Sent
                @endif
                <span style="font-weight: normal; font-size: smaller;">(Total: {{ count($reportData[$group]) }})</span>
            </h4>
            <table class="table table-condensed table-striped table-hover applicationTable">
                <thead>
                <tr>
                    <th>Center</th>
                    <th>First</th>
                    <th>Last</th>
                    <th style="text-align: center">Year</th>
                    <th>Incoming</th>
                    <th>Reg Date</th>
                @if ($group == 'withdrawn')
                        <th>Reason</th>
                        <th>Withdraw</th>
                    @elseif ($group == 'out')
                        <th>App Out</th>
                        <th>Due</th>
                    @elseif ($group == 'waiting')
                        <th>App In</th>
                        <th>Due</th>
                    @elseif ($group == 'approved')
                        <th>Approved</th>
                    @else
                        <th>Due</th>
                    @endif

                    <th>Comments</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reportData[$group] as $registrationData)
                    @if ($registrationData->due() && $registrationData->due()->lt($reportingDate))
                        <tr title="{{ ($group == 'notSent') ? 'Due within 2 days of registration' : 'Due within 14 days of registration' }}">
                    @else
                        <tr>
                            @endif
                            <td>{{ $registrationData->center->name }}</td>
                            <td>{{ $registrationData->firstName }}</td>
                            <td>{{ $registrationData->lastName }}</td>
                            <td style="text-align: center">{{ $registrationData->registration->teamYear }}</td>
                            <td>{{ $registrationData->incomingQuarter ? $registrationData->incomingQuarter->startWeekendDate->format('M') : '' }}</td>
                            <td>{{ $registrationData->regDate ? $registrationData->regDate->format('n/j/y') : '' }}</td>
                            @if ($group == 'withdrawn')
                                @if ($registrationData->withdrawCode)
                                    <td title="{{ $registrationData->withdrawCode->code }}">
                                        {{ $registrationData->withdrawCode->display }}
                                    </td>
                                    <td title="{{ $registrationData->withdrawCode->code }}">
                                        {{ $registrationData->wdDate ? $registrationData->wdDate->format('n/j/y') : '' }}
                                    </td>
                                @else
                                    <td></td>
                                    <td></td>
                                @endif
                            @elseif ($group == 'out')
                                <td>{{ $registrationData->appOutDate ? $registrationData->appOutDate->format('n/j/y') : '' }}</td>
                                <td {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                    {{ $registrationData->due() ? $registrationData->due()->format('n/j/y') : '' }}
                                </td>
                            @elseif ($group == 'waiting')
                                <td>{{ $registrationData->appInDate ? $registrationData->appInDate->format('n/j/y') : '' }}</td>
                                <td {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                    {{ $registrationData->due() ? $registrationData->due()->format('n/j/y') : '' }}
                                </td>
                            @elseif ($group == 'approved')
                                <td>{{ $registrationData->apprDate ? $registrationData->apprDate->format('n/j/y') : '' }}</td>
                            @else
                                <td {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                    {{ $registrationData->due() ? $registrationData->due()->format('n/j/y') : '' }}
                                </td>
                            @endif
                            <td>{{ is_numeric($registrationData->comment) ? TmlpStats\Util::getExcelDate($registrationData->comment)->format('F') : $registrationData->comment }}</td>
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
    $(document).ready(function() {
        $('table.applicationTable').dataTable({
            "paging":    false,
            "searching": false
        });
    });
</script>
