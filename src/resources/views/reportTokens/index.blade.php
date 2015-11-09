@extends('template')

@section('content')
    <h2>Report Tokens</h2>
    <a href="{{ url('/reporttokens/create') }}">+ Add one</a>
    <br/><br/>

    <div class="table-responsive">
        <table id="mainTable" class="table table-hover">
            <thead>
            <tr>
                <th>Report Date</th>
                <th>Center</th>
                <th>Expires</th>
                <th>Link</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($reportTokens as $token)
                <tr>
                    <td><a href="{{ url($token->getReportPath()) }}">{{ $token->report->reportingDate->format('M j, Y') }}</a></td>
                    <td>{{ $token->center ? $token->center->name : 'None' }}</td>
                    <td>{{ $token->expiresAt ? $token->expiresAt->format('M j, Y') : 'Never' }}</td>
                    <td width="70%"><input type="text" value="{{ url($token->getUrl()) }}" size="100" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#mainTable').dataTable({
                "paging":    false,
                "searching": false
            });
        });
    </script>
@endsection
