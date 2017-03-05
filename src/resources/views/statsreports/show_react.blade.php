@extends('template')
@inject('context', 'TmlpStats\Api\Context')

@section('content')
<?php
$nextQtrAccountabilities = $centerReportingDate->canShowNextQtrAccountabilities();
?>

@if ($statsReport)
    <h2>
        {{ $statsReport->center->name }} - {{ $statsReport->reportingDate->format('F j, Y') }}
        @if ($reportToken)
            &nbsp;<a class="reportLink" href="#" data-toggle="modal" data-target="#reportLinkModel">(View Report Link)</a>
        @endif
    </h2>
    @can ('index', TmlpStats\StatsReport::class)
    <div class="row">
        <div class="col-sm-10">
            <a href="{{ url('/home') }}"><< See All</a><br/><br/>
        </div>
        <div class="col-sm-1 hide-mobile" style="text-align: right">
            @if (isset($lastReport))
                <a href="{{ $lastReport->getUriLocalReport() }}"><< Last</a><br/><br/>
            @endif
        </div>
        <div class="col-sm-1 hide-mobile">
            @if (isset($nextReport))
                <a href="{{ $nextReport->getUriLocalReport() }}">Next >></a><br/><br/>
            @endif
        </div>
    </div>
    @endcan
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
    <div id="content">
        <div id="react-routed-flow"></div>
    </div>

    <div id="loader" style="display: none">
        @include('partials.loading')
    </div>

@else
    <p>Unable to find report.</p>
@endif

@endsection
