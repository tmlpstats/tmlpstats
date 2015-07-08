
    <div class="form-group">
        {!! Form::label('reporting_date', 'Reporting Date:', ['class' => 'col-sm-1 control-label']) !!}
        <div class="col-sm-2">
            {!! Form::select('reporting_date', $reportingDates, null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="btn-group col-sm-offset-2">
        {!! link_to($submitButtonText == 'Create' ? url('admin/globalreports') : URL::previous(), 'Cancel', ['class' => 'btn btn-default']) !!}
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-default btn-primary']) !!}
    </div>
