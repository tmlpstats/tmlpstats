@extends('template')

@section('content')
<h2 class="sub-header">Stats Reports</h2>
{!! Form::open(['url' => 'admin/statsreports', 'method' => 'GET', 'class' => 'form-horizontal']) !!}
<div class="form-group">
    {!! Form::label('stats_report', 'Week:', ['class' => 'col-sm-1 control-label']) !!}
    <div class="col-sm-2">
        {!! Form::select('stats_report', $reportingDates, $reportingDate->toDateString(), ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
    </div>
</div>

@include('partials.regions')
{!! Form::close() !!}
<br/><br/>

<div class="table-responsive">
    <table id="activeCenterTable" class="table table-hover">
        <thead>
        <tr>
            <th>Center</th>
            <th>Global Region</th>
            <th>Local Region</th>
            <th>Reporting Date</th>
            <th>Rating</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($statsReportList as $centerName => $statsReportData)
        @if ($statsReportData['report'])
            <?php $statsReport = $statsReportData['report']; ?>
            <?php $center = $statsReportData['center']; ?>
            <tr class="{{ $statsReport->validated ? 'success' : 'danger' }}" >
                <td>{{ $centerName }}</td>
                <td>{{ $center->globalRegion }}</td>
                <td>{{ $center->localRegion ?: '-' }}</td>
                <td>{{ $statsReport->reportingDate->format('F j, Y') }}</td>
                <td>
                    @if ($statsReport->centerStats && $statsReport->centerStats->actualData)
                        {{ $statsReport->centerStats->actualData->rating }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center">
                    <a href="{{ url('/admin/statsreports/' . $statsReport->id) }}" class="view" title="View" style="color: black">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                </td>
                <td style="text-align: center">
                    <a href="{{ url('/admin/statsreports/' . $statsReport->id . '/edit') }}" title="Edit" style="color: black"><span class="glyphicon glyphicon-edit"></span></a>
                </td>
            </tr>
        @else
            <?php $center = $statsReportData['center']; ?>
            <tr class="danger">
                <td>{{ $centerName }}</td>
                <td>{{ $center->globalRegion }}</td>
                <td>{{ $center->localRegion ?: '-' }}</td>
                <td>-</td>
                <td>-</td>
                <td></td>
                <td></td>
            </tr>
        @endif
        @endforeach
        </tbody>
    </table>
</div>

<script src="{{ asset('/js/query.dataTables.min.js') }}"></script>
<script src="{{ asset('/js/dataTables.bootstrap.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#activeCenterTable').dataTable({
            "paging":    false,
            "searching": false
        });
        $('#inactiveCenterTable').dataTable({
            "paging":    false,
            "searching": false
        });
    });
</script>
@endsection
