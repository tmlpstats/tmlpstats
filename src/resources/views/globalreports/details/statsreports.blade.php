<div class="table-responsive">

    <br/>
    @include('reports.summaryboxes', compact('boxes'))

    <br/>
    <table id="centerReportsTable" class="table want-datatable">
        <thead>
        <tr>
            <th>Center</th>
            <th>Region</th>
            <th class="data-point">On Time</th>
            <th>Submit Time</th>
            <th class="data-point">Had Revision</th>
            <th>Revision Time</th>
            <th>Rating</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($statsReportsList as $data)
            <tr id="{{ $data['id'] }}"
                class="{{ !$data['onTime'] ? ' bg-danger' : (isset($data['revisionSubmitTime']) ? 'bg-warning' : '') }}">
                <td>
                    @statsReportLink($data['officialReport'])
                        {{ $data['center'] }}
                    @endStatsReportLink
                </td>
                <td>{{ $data['region'] }}</td>
                <td class="data-point">{{ $data['onTime'] ? 'Yes' : 'No' }}</td>
                <td>
                    @statsReportLink($data['officialReport'])
                        {{ $data['officialSubmitTime'] }}
                    @endStatsReportLink
                </td>
                @if (isset($data['revisedReport']))
                    <td class="data-point">Yes</td>
                    <td>
                        @statsReportLink($data['revisedReport'])
                            {{ $data['revisionSubmitTime'] }}
                        @endStatsReportLink
                    </td>
                @else
                    <td class="data-point">No</td>
                    <td></td>
                @endif
                <td>
                    @if ($data['isValidated'])
                        {{ $data['rating'] }} ({{ $data['points'] }})
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
