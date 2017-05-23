
    {!! Form::hidden('id') !!}
    <div class="form-group">
        {!! Form::label('name', 'Name:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('abbreviation', 'Abbreviation:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('abbreviation', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <!--
    <div class="form-group">
        {!! Form::label('team_name', 'Team Name:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('team_name', null, ['class' => 'form-control']) !!}
        </div>
    </div>
    -->

    <div class="form-group">
        {!! Form::label('stats_email', 'Stats Email:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('stats_email', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('region', 'Region:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            @include('partials.forms.regions', ['includeLocalRegions' => true, 'selectedRegion' => isset($center) ? $center->region->abbreviation : null])
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('timezone', 'Timezone:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            @include('partials.forms.timezones')
        </div>
    </div>

    <!--
    <div class="form-group">
        {!! Form::label('sheet_filename', 'Sheet Filename:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('sheet_filename', null, ['class' => 'form-control']) !!}
        </div>
    </div>
    -->

    <div class="form-group">
        {!! Form::label('sheet_version', 'Sheet Version:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('sheet_version', null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('mailing_list', 'Mailing List:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('mailing_list', isset($center) ? implode(', ', $center->getMailingList($quarter)) : null, ['class' => 'form-control']) !!}
            <p class="help">List additional email addresses to include when sending reports. Separate with a comma to include multiple.</p>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('active', 'Active:', ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::checkbox('active', 1, 1, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="btn-group col-sm-offset-2">
        {!! link_to($submitButtonText == 'Create' ? url('admin/centers') : URL::previous(), 'Cancel', ['class' => 'btn btn-default']) !!}
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-default btn-primary']) !!}
    </div>
