<div class="form-group">
    {!! Form::label('region', 'Region:', ['class' => 'col-sm-1 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::select('region',[
                'ANZ' => 'Australia/New Zealand',
                'EME' => 'Europe/Middle East',
                'IND' => 'India',
                'NA'  => 'North America'
            ], $selectedRegion, ['class' => 'form-control',  'onchange' => 'this.form.submit()']) !!}
    </div>
</div>