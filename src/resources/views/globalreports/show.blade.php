@extends('template')

@section('content')
    <div id="content">
        <h2>{{ $region ? $region->name : 'Global' }} Report - {{ $globalReport->reportingDate->format('F j, Y') }}</h2>
        <a href="{{ \URL::previous() }}"><< Go Back</a><br/><br/>

        {!! Form::open(['url' => "globalreports/{$globalReport->id}", 'method' => 'GET', 'class' => 'form-horizontal', 'id' => 'reportSelectorForm']) !!}
        <div class="form-group">
            {!! Form::label('region', 'Region:', ['class' => 'col-sm-1 control-label']) !!}
            <div class="col-sm-3">
                @include('partials.forms.regions', ['selectedRegion' => $region->abbreviation, 'includeLocalRegions' => true])
            </div>
        </div>
        {!! Form::close() !!}

        <div class="col-xs-2">
            <ul id="tabs " class="nav nav-tabs tabs-left" data-tabs="tabs">
                <li class="active"><a href="#ratingsummary-tab" data-toggle="tab">Ratings Summary</a></li>
                <li><a href="#regionalstats-tab" data-toggle="tab">Regional Games</a></li>
                <li><a href="#statsreports-tab" data-toggle="tab">Center Reports</a></li>
            </ul>
        </div>
        <div class="col-xs-10">
            <div class="tab-content">
                <div class="tab-pane active" id="ratingsummary-tab">
                    <h3>Ratings Summary</h3>

                    <div id="ratingsummary-container">
                        @include('partials.loading')
                    </div>
                </div>
                <div class="tab-pane" id="regionalstats-tab">
                    <h3>Regional Games</h3>

                    <div id="regionalstats-container">
                        @include('partials.loading')
                    </div>
                </div>
                <div class="tab-pane" id="statsreports-tab">
                    <h3>Center Reports</h3>

                    <div id="statsreports-container">
                        @include('partials.loading')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#tabs').tab();

            // Fetch Rating Summary
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/ratingsummary?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#ratingsummary-container").html(response);
                }
            });
            // Fetch Regional Stats
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/regionalstats?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#regionalstats-container").html(response);
                }
            });
            // Fetch Regional Stats
            $.ajax({
                type: "GET",
                url: "{{ url("/globalreports/{$globalReport->id}/statsreports?region={$region->abbreviation}") }}",
                success: function (response) {
                    $("#statsreports-container").html(response);
                }
            });
        });
    </script>
@endsection
