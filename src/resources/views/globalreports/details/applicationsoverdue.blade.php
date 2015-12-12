<div class="table-responsive">
    @foreach (['notSent', 'out', 'waiting'] as $group)
        @if ($reportData[$group])
            <br/>
            <h4>
                @if ($group == 'out')
                    Application Out
                @elseif ($group == 'waiting')
                    Awaiting Interview
                @else
                    Application Not Sent
                @endif
                <span style="font-weight: normal; font-size: smaller;">(Total: {{ count($reportData[$group]) }})</span>
            </h4>
            <table class="table table-condensed table-striped table-hover applicationsOverdueTable">
                <thead>
                <tr>
                    <th>Center</th>
                    <th>Name</th>
                    <th class="data-point">Year</th>
                    <th class="data-point">Reg Date</th>
                    @if ($group == 'out')
                        <th class="data-point">App Out</th>
                        <th class="data-point">Due</th>
                    @elseif ($group == 'waiting')
                        <th class="data-point">App In</th>
                        <th class="data-point">Due</th>
                    @else
                        <th class="data-point">Due</th>
                    @endif
                    <th>Comments</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reportData[$group] as $registrationData)
                    <tr title="{{ ($group == 'notSent') ? 'Due within 2 days of registration' : 'Due within 14 days of registration' }}">

                        <td>
                            @statsReportLink($registrationData->statsReport)
                                {{ $registrationData->center->name }}
                            @endStatsReportLink
                        </td>
                        <td>{{ $registrationData->firstName }} {{ $registrationData->lastName }}</td>
                        <td class="data-point">{{ $registrationData->registration->teamYear }}</td>
                        <td class="data-point">
                            @if ($registrationData->regDate)
                                @date($registrationData->regDate)
                            @endif
                        </td>
                        @if ($group == 'out')
                            <td class="data-point">
                                @if ($registrationData->appOutDate)
                                    @date($registrationData->appOutDate)
                                @endif
                            </td>
                            <td class="data-point" {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                @if($registrationData->due())
                                    @date($registrationData->due())
                                @endif
                            </td>
                        @elseif ($group == 'waiting')
                            <td class="data-point">
                                @if ($registrationData->appInDate)
                                    @date($registrationData->appInDate)
                                @endif
                            </td>
                            <td class="data-point" {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                @if($registrationData->due())
                                    @date($registrationData->due())
                                @endif
                            </td>
                        @else
                            <td class="data-point" {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                @if($registrationData->due())
                                    @date($registrationData->due())
                                @endif
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

<script type="text/javascript">
    $(document).ready(function() {
        $('table.applicationsOverdueTable').dataTable({
            "paging":    false,
            "searching": false
        });
    });
</script>
