@extends('template')
@inject('context', 'TmlpStats\Api\Context')
@section('content')
    <h1>Report Not Found</h1>

    <div id="content">
        We couldn't find a report for <b>{{ $reportingDate->toDateString() }}</b>. {{ $reason ?? '' }}

        @if ($maybeReportDate && $maybeReportUrl)
            <p>
                However, we do have a report for <b>{{ $maybeReportDate->toDateString() }}</b>
            </p>
            <p>
                <a href="{{ $maybeReportUrl }}" class="btn btn-lg btn-default">Click Here</a>
            </div>
        @endif
    </div>

@endsection

@section('scripts')
@if ($maybeReportDate && $maybeReportUrl)
    <script type="text/javascript">
        setTimeout(function() {
            document.location.href = @json($maybeReportUrl);
        }, 3000);
    </script>
@endif
@endsection
