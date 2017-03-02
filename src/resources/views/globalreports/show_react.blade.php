@extends('template')
@inject('context', 'TmlpStats\Api\Context')

@section('content')
    <div id="content">
        @include('globalreports._show_head')

        <div id="submission-flow"></div>

        <div id="loader" style="display: none">
            @include('partials.loading')
        </div>
    </div>
@endsection
