<div class="table-responsive">
    <div id="errors" class="alert alert-danger" role="alert" style="display:none">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <span class="message-prefix" style="font-weight:bold">Error: </span>
        <span class="message"></span>
    </div>

    <table id="activeCenterTable" class="table">
        <thead>
        <tr>
            <th>Center</th>
            <th>Region</th>
            <th>On Time</th>
            <th>Submit Time</th>
            <th>Had Revision</th>
            <th>Revision Time</th>
            <th>Rating</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($statsReportsList as $data)
            <tr id="{{ $data['id'] }}"
                class="{{ !$data['onTime'] ? ' bg-danger' : (isset($data['revisionSubmitTime']) ? 'bg-warning' : '') }}">
                <td>
                    @can ('read', $data['officialReport'])
                    <a href="{{ url("/statsreports/{$data['id']}") }}">
                        {{ $data['center'] }}
                    </a>
                    @else
                        {{ $data['center'] }}
                    @endcan
                </td>
                <td>{{ $data['region'] }}</td>
                <td style="text-align: center">{{ $data['onTime'] ? 'Yes' : 'No' }}</td>
                <td>
                    @can ('read', $data['officialReport'])
                    <a href="{{ url("/statsreports/{$data['officialReport']->id}") }}">
                        {{ $data['officialSubmitTime'] }}
                    </a>
                    @else
                        {{ $data['officialSubmitTime'] }}
                    @endcan
                </td>
                @if (isset($data['revisedReport']))
                    <td style="text-align: center">Yes</td>
                    <td>
                        @can ('read', $data['revisedReport'])
                        <a href="{{ url("/statsreports/{$data['revisedReport']->id}") }}">
                            {{ $data['revisionSubmitTime'] }}
                        </a>
                        @else
                            {{ $data['revisionSubmitTime'] }}
                        @endcan
                    </td>
                @else
                    <td style="text-align: center">No</td>
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
