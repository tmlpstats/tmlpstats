@extends('template')
@inject('context', 'TmlpStats\Api\Context')

@section('content')
    <div id="content">
        <h2>
            {{ $region ? $region->name : 'Global' }} Report - {{ $globalReport->reportingDate->format('F j, Y') }}
        </h2>
        <a href="{{ \URL::previous() }}"><< Go Back</a><br/><br/>
        <br />

    <div id="globalreport-dest"></div>

    <div id="loader" style="display: none">
        @include('partials.loading')
    </div>

    <script type="text/javascript">
    $(function() {
        showReportView({
            report: 'Global',
            target: '#globalreport-dest',
            report_override: {
                "root": [
                    "{{ $reportName }}",
                ],
                "children": {
                    "{{ $reportName }}": {
                        "id": "{{ $reportName }}",
                        "n": 2,
                        "type": "report",
                        "name": "{{ $reportName }}"
                    },
                },
            },
            params: {
                globalReport: @json($globalReport->id),
                region: @json($region->abbreviation)
            },
            pastClassroom2: @json($globalReport->reportingDate->gte($quarter->getClassroom2Date())),
        })
    })
    </script>
@endsection
