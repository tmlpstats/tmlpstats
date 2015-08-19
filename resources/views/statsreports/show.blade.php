@extends('template')

@section('content')

@if ($statsReport)
<h2>{{ $statsReport->center->name }} - {{ $statsReport->reportingDate->format('F j, Y') }}</h2>
<a href="{{ url('/statsreports') }}"><< See All</a><br/><br/>

<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <tr>
            <th>Center:</th>
            <td>{{ $statsReport->center->name }}</td>
        </tr>
        <tr>
            <th>Region:</th>
            <td>
                {{ $statsReport->center->global_region }}
                @if ($statsReport->center->local_region)
                 - {{ $statsReport->center->local_region }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Stats Email:</th>
            <td>{{ $statsReport->center->stats_email }}</td>
        </tr>
        <tr>
            <th>Submitted Sheet Version:</th>
            <td>{{ $statsReport->spreadsheet_version }}</td>
        </tr>
        <tr>
            <th>Rating:</th>
            <td>
                @if ($statsReport->centerStats && $statsReport->centerStats->actualData)
                    {{ $statsReport->centerStats->actualData->rating }}
                @else
                    -
                @endif
            </td>
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
<?php $center = $sheet['center'] ?: 'Unknown'; ?>
<?php $reportingDate = $sheet['reportingDate'] ? $sheet['reportingDate']->format('M j, Y') : 'Unknown'; ?>
<?php $sheetVersion = $sheet['sheetVersion'] ?: 'Unknown'; ?>
    <li class="<?= $sheet['result'] ?>">
        <?= $center.": ".$reportingDate." - sheet v".$sheetVersion ?>
        @if ($sheet['result'] != 'ok')
            <?php
            $messages = array();
            foreach ($sheet['errors'] as $msg) {
                if (isset($msg['offset'])) {
                    $messages[$msg['section']][$msg['offset']]['offsetType'] = $msg['offsetType']; // ugly, but works
                    $messages[$msg['section']][$msg['offset']][] = $msg;
                } else {
                    $messages[$msg['section']][0][] = $msg;
                }
            }
            foreach ($sheet['warnings'] as $msg) {
                if (isset($msg['offset'])) {
                    $messages[$msg['section']][$msg['offset']]['offsetType'] = $msg['offsetType']; // ugly, but works
                    $messages[$msg['section']][$msg['offset']][] = $msg;
                } else {
                    $messages[$msg['section']][0][] = $msg;
                }
            }
            // Presort by row
            foreach ($messages as $section => &$data) {
                if (count($data) <= 1) continue;

                ksort($data);
            }
            ?>
            <ul>
                @foreach ($messages as $tabName => $tab)
                    <li>{{ ucfirst($tabName) }}
                        <ul>
                        @foreach ($tab as $offset => $offsetMessages)
                            @if ($offset === 0)
                                @foreach ($offsetMessages as $msg)
                                    @if (is_array($msg))
                                        <li class="{{ $msg['type'] }}">{{ $msg['message'] }}</li>
                                    @endif
                                @endforeach
                            @else
                            <li>{{ ucfirst($offsetMessages['offsetType']) . " " . $offset }}
                                <ul>
                                @foreach ($offsetMessages as $msg)
                                    @if (is_array($msg))
                                        <li class="{{ $msg['type'] }}">{{ $msg['message'] }}</li>
                                    @endif
                                @endforeach
                                </ul>
                            </li>
                            @endif
                        @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @endif
    </li>
@endif

@else
<p>Unable to find report.</p>
@endif

@endsection
