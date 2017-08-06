@extends('template')
@inject('context', 'TmlpStats\Api\Context')
@section('content')

@if ($statsReport)
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
