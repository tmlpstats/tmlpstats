@extends('template')

@section('content')

@if ($statsReport)
<h2>{{ $statsReport->center->name }} - {{ $statsReport->reportingDate->format('F j, Y') }}</h2>
<!-- <a href="{{ url('/statsreports') }}"><< See All</a><br/><br/> -->

<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <tr>
            <th>Center:</th>
            <td>{{ $statsReport->center->name }}</td>
        </tr>
        <tr>
            <th>Region:</th>
            <td>
                <?php
                $region = $statsReport->center->getLocalRegion();
                if ($region) {
                    echo $region->name;
                }
                ?>
                {{ $statsReport->center->global_region }}
                @if ($statsReport->center->getLocalRegion())
                 - <?php
                    $region = $statsReport->center->getLocalRegion();
                    if ($region) {
                        echo $region->name;
                    }
                 ?>
                @endif
            </td>
        </tr>
        <tr>
            <th>Stats Email:</th>
            <td>{{ $statsReport->center->stats_email }}</td>
        </tr>
        <tr>
            <th>Submitted At:</th>
            <td><?php
                if ($statsReport->submittedAt) {
                    $submittedAt = clone $statsReport->submittedAt;
                    $submittedAt->setTimezone($statsReport->center->timezone);
                    echo $submittedAt->format('l, F jS \a\t g:ia T');
                } else {
                    echo '-';
                }
            ?></td>
        </tr>
        <tr>
            <th>Submitted Sheet Version:</th>
            <td>{{ $statsReport->spreadsheet_version }}</td>
        </tr>
        <tr>
            <th>Rating:</th>
            <td>
                @if ($statsReport)
                    {{ $statsReport->getRating() }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>File:</th>
            <td>
                @if ($sheetUrl)
                    <a href="{{ $sheetUrl }}">Download</a>
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>Submission Comment:</th>
            <td>{{ $statsReport->submitComment }}</td>
        </tr>
<!--         <tr>
            <th>Locked:</th>
            <td><i class="fa {{ $statsReport->locked ? 'fa-lock' : 'fa-unlock' }}"></i></td>
        </tr>
        <tr>
            <th>Global Report:</th>
            <td>
                @if ($statsReport->globalReports->isEmpty())

                    Not in report
                @else
                    <a href="{{ url('/globalreports/' . $statsReport->globalReports->first()->id ) }}">{{ $statsReport->globalReports()->first()->reportingDate->format('M j, Y') }}</a>
                @endif
            </td>
        </tr> -->
    </table>
</div>

@if ($sheet)
<h4>Results:</h4>
@include('import.results', ['sheet' => $sheet, 'includeUl' => true])
@endif

@else
<p>Unable to find report.</p>
@endif

@endsection
