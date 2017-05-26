@extends('template')
@inject('context', 'TmlpStats\Api\Context')
@section('content')
    <h1>Report Not Found</h1>

    <div id="content">
        We couldn't find a report for <b>{{ $reportingDate->toDateString() }}</b>

        @if ($maybeReport)
            <p>
                However, we do have a report for <b>{{ $maybeReport->reportingDate->toDateString() }}</b>
            </p>
            <p>
                <a href="{{ $maybeReport->getUriLocalReport() }}" class="btn btn-lg btn-default">Click Here</a>
            </div>
        @endif
    </div>

@endsection

@section('scripts')
@if ($maybeReport)
    <script type="text/javascript">
        setTimeout(function() {
            document.location.href = @json($maybeReport->getUriLocalReport());
        }, 3000);
    </script>
@endif
@endsection
