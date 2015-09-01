@extends('template')

@section('content')
    <h1>Import Stats Sheet</h1>

    @if ($showUploadForm)
        @include('import.form', ['formAction' => '/admin/import', 'showReportCheckSettings' => $showReportCheckSettings])
    @endif

    @if (isset($results))
        @include('import.upload', ['results' => $results])
    @endif
@endsection
