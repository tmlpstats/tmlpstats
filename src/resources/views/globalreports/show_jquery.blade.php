@extends('template')
@inject('context', 'TmlpStats\Api\Context')

@section('content')
    <div id="content">
        <h2>
            {{ $region ? $region->name : 'Global' }} Report - {{ $globalReport->reportingDate->format('F j, Y') }}
            @if ($reportToken)
                &nbsp;<a class="reportLink" href="#" data-toggle="modal" data-target="#reportLinkModel">(View Report Link)</a>
            @endif
        </h2>
        <a href="{{ \URL::previous() }}"><< Go Back</a><br/><br/>

        @if ($reportToken)
            <div class="modal fade" id="reportLinkModel" tabindex="-1" role="dialog" aria-labelledby="reportLinkModelLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="reportLinkModelLabel">Report Link</h4>
                        </div>
                        <div class="modal-body">
                            <textarea id="reportTokenUrl" rows="2" cols="78" >{{ url($reportToken->getUrl()) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <br />

    <div id="globalreport-dest"></div>

    <div id="loader" style="display: none">
        @include('partials.loading')
    </div>
    <br />
    <br />
    <small>This is a tech demo of our faster loading Global Report. If this page is not displaying properly, <a href="{{ $context->dateSelectAction('RD') }}?viewmode=html">click here</a></small>

    <script type="text/javascript">
    $(function() {
        showReportView({
            report: 'Global',
            target: '#globalreport-dest',
            params: {
                globalReport: @json($globalReport->id),
                region: @json($region->abbreviation)
            },
            pastClassroom2: @json($globalReport->reportingDate->gte($quarter->getClassroom2Date())),
        })
    })
    </script>
@endsection
