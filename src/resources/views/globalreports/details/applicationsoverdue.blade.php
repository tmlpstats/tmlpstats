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
            <table class="table table-condensed table-striped table-hover applicationTable">
                <thead>
                <tr>
                    <th>Center</th>
                    <th>Name</th>
                    <th style="text-align: center">Year</th>
                    <th>Reg Date</th>
                    @if ($group == 'out')
                        <th>App Out</th>
                        <th>Due</th>
                    @elseif ($group == 'waiting')
                        <th>App In</th>
                        <th>Due</th>
                    @else
                        <th>Due</th>
                    @endif
                    <th>Comments</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reportData[$group] as $registrationData)
                    <tr title="{{ ($group == 'notSent') ? 'Due within 2 days of registration' : 'Due within 14 days of registration' }}">

                        <td>{{ $registrationData->center->name }}</td>
                        <td>{{ $registrationData->firstName }} {{ $registrationData->lastName }}</td>
                        <td style="text-align: center">{{ $registrationData->registration->teamYear }}</td>
                        <td>{{ $registrationData->regDate ? $registrationData->regDate->format('n/j/y') : '' }}</td>
                        @if ($group == 'out')
                            <td>{{ $registrationData->appOutDate ? $registrationData->appOutDate->format('n/j/y') : '' }}</td>
                            <td {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                {{ $registrationData->due() ? $registrationData->due()->format('n/j/y') : '' }}
                            </td>
                        @elseif ($group == 'waiting')
                            <td>{{ $registrationData->appInDate ? $registrationData->appInDate->format('n/j/y') : '' }}</td>
                            <td {!! ($registrationData->due() && $registrationData->due()->lt($reportingDate)) ? 'style="color: red"' : '' !!}>
                                {{ $registrationData->due() ? $registrationData->due()->format('n/j/y') : '' }}
                            </td>
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
