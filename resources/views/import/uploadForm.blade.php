{!! Form::open(['url' => $formAction, 'files' => true]) !!}
<div class="form-group">
    {!! Form::label('statsFiles[]', 'Center Stats File:') !!}
    {!! Form::file('statsFiles[]', [
            'class'=> 'file form-control',
            'multiple' => true,
            'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel.sheet.macroEnabled.12'
        ])
    !!}
    <p class="help-block">Up to 20 sheets at a time seems to work. Please be patient. It takes about 30 seconds per sheet.</p>
</div>
@if ($showReportCheckSettings)
<div class="form-group">
    {!! Form::label('expectedReportDate', 'Report Date:') !!}
    {!! Form::input('date', 'expectedReportDate', old('expectedReportDate', $expectedDate), ['class'=> 'form-control']) !!}
</div>
<div class="checkbox">
    <label>
        {!! Form::checkbox('ignoreReportDate', 1, old('ignoreReportDate', false)) !!} Ignore Report Date
    </label>
</div>
<div class="checkbox">
    <label>
        {!! Form::checkbox('ignoreVersion', 1, old('ignoreVersion', false)) !!} Ignore sheet version enforcement
    </label>
</div>
@endif

{!! Form::submit('Go', ['class' => 'btn btn-default btn-primary']) !!}

{!! Form::hidden('validate_only', true) !!}
{!! Form::close() !!}



@section('scripts')
<script type="text/javascript">
    $(function($) {
        $("input[name='ignoreReportDate']").click(function(){
            if ($(this).is(':checked')) {
                $('#expectedReportDate').attr("disabled", true);
            } else if ($(this).not(':checked')) {
                $('#expectedReportDate').removeAttr("disabled");
            }
        });
    });
</script>
@stop
