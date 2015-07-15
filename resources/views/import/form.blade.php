{!! Form::open(['url' => $formAction, 'files' => true]) !!}
<div class="form-group">
    {!! Form::label('statsFiles[]', 'Center Stats File:') !!}
    {!! Form::file('statsFiles[]', [
            'class'=> 'file form-control',
            'multiple' => true,
            'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel.sheet.macroEnabled.12'
        ])
    !!}
</div>
@if ($showReportCheckSettings)
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <a class="advanced" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="color: black">
                Advanced <span id="advanced-state" class="glyphicon glyphicon-menu-right" />
            </a>
        </div>
        <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                <div class="form-group">
                    {!! Form::label('expectedReportDate', 'Report Date:') !!}
                    {!! Form::input('date', 'expectedReportDate', old('expectedReportDate', $expectedDate), ['class'=> 'form-control']) !!}
                </div>
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('ignoreReportDate', 1, old('ignoreReportDate', false)) !!} Ignore report date
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('ignoreVersion', 1, old('ignoreVersion', false)) !!} Ignore sheet version enforcement
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{!! Form::submit('Go', ['id' => 'go', 'class' => 'btn btn-default btn-primary']) !!}
<div id="updating" style="display:none"><br/><p>Please be patient. <span style="font-weight:bold">It may take up to 1 minute to complete.</span></div>

{!! Form::hidden('submitReport', $submitReport) !!}
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
        $("#go").click(function(){
            $("#updating").show();
            $("#results").hide();
        });
        $("a.advanced").click(function(){
            if ($("#advanced-state").hasClass("glyphicon-menu-right")) {
                $("#advanced-state").removeClass("glyphicon-menu-right");
                $("#advanced-state").addClass("glyphicon-menu-down");
            } else {
                $("#advanced-state").removeClass("glyphicon-menu-down");
                $("#advanced-state").addClass("glyphicon-menu-right");
            }
        });
    });
</script>
@stop
