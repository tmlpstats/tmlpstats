@extends('template')

@section('content')
    <h1>Validate Stats Sheet</h1>

    @if ($showUploadForm)
        @include('import.uploadForm', ['formAction' => '/import', 'expectedDate' => $expectedDate, 'showReportCheckSettings' => $showReportCheckSettings])
    @endif

    @if (isset($results))
        @include('import.upload', ['results' => $results])
    @endif
@endsection