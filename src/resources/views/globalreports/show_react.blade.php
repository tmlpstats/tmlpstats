@extends('template')
@inject('context', 'TmlpStats\Api\Context')

@section('content')
    <div id="content">
        <div id="react-routed-flow"></div>

        <div id="loader" style="display: none">
            @include('partials.loading')
        </div>
    </div>
@endsection
