@extends('template')

@section('content')
<h2>Add a Global Report</h2>

@include('errors.list')

{!! Form::open(['url' => 'admin/globalreports', 'class' => 'form-horizontal']) !!}

    <div class="form-group">
        {!! Form::label('reporting_date', 'Reporting Date:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-2">
            {!! Form::select('reporting_date', $reportingDates, null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="btn-group col-sm-offset-2">
        {!! link_to(url('admin/globalreports'), 'Cancel', ['class' => 'btn btn-default']) !!}
        {!! Form::submit('Create', ['class' => 'btn btn-default btn-primary']) !!}
    </div>

{!! Form::close() !!}

@endsection