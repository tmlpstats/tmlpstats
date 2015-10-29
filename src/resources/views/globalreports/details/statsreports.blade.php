@if (!$globalReport->statsReports)
    <p>No report information available.</p>
@else
<div class="table-responsive">
    <div id="errors" class="alert alert-danger" role="alert" style="display:none">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <span class="message-prefix" style="font-weight:bold">Error: </span>
        <span class="message"></span>
    </div>

    <table class="table table-condensed table-striped">
        <tr>
            <th>Reporting Date:</th>
            <td>{{ $globalReport->reportingDate->format('F j, Y') }}</td>
        </tr>
        <tr>
            <th>Stats Reports:</th>
            <td>
                <table id="activeCenterTable" class="table table-hover">
                    <thead>
                    <tr>
                        <th>Center</th>
                        <th>Region</th>
                        <th>On Time</th>
                        <th>Submit Time</th>
                        <th>Rating</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($statsReportsList as $data)
                        <tr id="{{ $data['id'] }}" class="{{ !$data['onTime'] ? 'danger' : '' }}" >
                            <td><a href="{{ url("/statsreports/{$data['id']}") }}">{{ $data['center'] }}</a></td>
                            <td>{{ $data['region'] }}</td>
                            <td style="text-align: center">{{ $data['onTime'] ? 'Yes' : 'No' }}</td>
                            <td>{{ isset($data['lastSubmitOnTime']) && $data['lastSubmitOnTime'] ? $data['lastSubmitTime'] : $data['firstSubmitTime'] }}</td>
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
            </td>
        </tr>
    </table>
</div>
@endif
