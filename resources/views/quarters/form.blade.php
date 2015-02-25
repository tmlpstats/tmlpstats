{!! Form::hidden('previous_url', URL::previous()) !!}

<div class="form-group">
    {!! Form::label('location', 'Location:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('location', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('distinction', 'Distinction:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::select('distinction', [
                'Relatedness'=>'Relatedness',
                'Possibility'=>'Possibility',
                'Opportunity'=>'Opportunity',
                'Action'=>'Action',
                'Completion'=>'Completion',
            ], null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('start_weekend_date', 'Quarter Start:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('date', 'start_weekend_date', isset($quarter) ? $quarter->start_weekend_date->format('Y-m-d') : null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('classroom1_date', 'Classroom 1:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('date', 'classroom1_date', isset($quarter) ? $quarter->classroom1_date->format('Y-m-d') : null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('classroom2_date', 'Classroom 2:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('date', 'classroom2_date', isset($quarter) ? $quarter->classroom2_date->format('Y-m-d') : null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('classroom3_date', 'Classroom 3:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('date', 'classroom3_date', isset($quarter) ? $quarter->classroom3_date->format('Y-m-d') : null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('end_weekend_date', 'Quarter End:', ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::input('date', 'end_weekend_date', isset($quarter) ? $quarter->end_weekend_date->format('Y-m-d') : null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="btn-group col-sm-offset-2">
        {!! link_to($submitButtonText == 'Create' ? url('admin/quarters') : URL::previous(), 'Cancel', ['class' => 'btn btn-default']) !!}
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-default btn-primary']) !!}
</div>