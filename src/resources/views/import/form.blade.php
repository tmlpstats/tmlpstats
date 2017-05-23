@if ($showAccountabilities)
    <div id="cr3-accountabilities" data-reportingDate="{{ $expectedDate }}"></div>
@endif

{!! Form::open(['url' => $formAction, 'files' => true]) !!}
@if ($showAccountabilities)
    <input type="hidden" name="showAccountabilities" value="1" />
@endif
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

@if ($submitReport)
<div class="alert alert-warning" role="alert">
<strong>Don't Forget:</strong> Your sheet isn't submitted yet.  Click 'Submit' to send your completed sheet to the regional stats team.
</div>
@else
<div class="alert alert-info" role="alert">
<strong>Helpful Hint:</strong> Click 'Validate' to check your sheet for errors. Then click 'Submit' to send your completed sheet to the regional stats team. You can validate as many times as you'd like before submitting.
</div>
@endif

{!! Form::submit('Validate', ['id' => 'validate', 'class' => 'btn btn-primary']) !!}
@if ($submitReport)
&nbsp;&nbsp;
{!! Form::button('Submit', ['id' => 'submit', 'class' => 'btn btn-success', 'data-toggle' => 'modal', 'data-target' => '#submitModel']) !!}
@endif
<div id="updating" style="display:none">
    <br/><p>Please be patient. <span style="font-weight:bold">It may take up to 1 minute to complete.</span>

    @include('partials.loading')
</div>

<div class="modal fade" id="selectFileModel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Choose file</h4>
            </div>
            <div class="modal-body">
                <p>Please select a file before clicking Validate.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="submitModel" tabindex="-1" role="dialog" aria-labelledby="submitModelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="submitModelLabel">Submit your stats</h4>
            </div>
            <div class="modal-body">
                Clicking submit will send your stats to the regional stats team. We will also send a copy to your
                <ul>
                    <li>Program Manager</li>
                    <li>Classroom Leader</li>
                    <li>Team 2 Team Leader</li>
                    <li>Team 1 Team Leader</li>
                    <li>Statistician</li>
                    <li>Statistician Apprentice</li>
                </ul>
                You can re-submit your stats before 7PM your local time on Friday.
                <br/><br/>
                {!! Form::open(['url' => $formAction]) !!}
                <label for="comment" class="control-label">Comment:</label>
                {!! Form::textarea('comment', null, ['class' => 'form-control', 'rows' => '10']) !!}
                {!! Form::close() !!}

                @include('partials.loading', ['show' => false, 'id' => 'submitLoading'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="submitStatsCancel">Cancel</button>
                <button type="button" class="btn btn-success" id="submitStats">Submit</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="submitCompleteModel" tabindex="-1" role="dialog" aria-labelledby="submitCompleteModelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="submitCompleteModelLabel">Your stats are submitted!</h4>
            </div>
            <div class="modal-body">
                <div id="successMessage">
                You have successfully submitted your stats! We received them at <span id="submitTime"></span>.

                Check to make sure you received an email from TMLP Stats in your center's stats email.<br/><br/>
                </div>
                <div id="submitResult" class="alert" role="alert" style="display:none">
                    <span class="message"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitteOk" data-dismiss="modal">Okay</button>
            </div>
        </div>
    </div>
</div>

{!! Form::hidden('submitReport', false) !!}
{!! Form::close() !!}



@section('scripts')
<script type="text/javascript">
    $(function($) {
        $("input[name='ignoreReportDate']").click(function() {
            if ($(this).is(':checked')) {
                $('#expectedReportDate').attr("disabled", true);
            } else if ($(this).not(':checked')) {
                $('#expectedReportDate').removeAttr("disabled");
            }
        });
        $("#validate").click(function() {

            if (!$("input[type=file]").val()) {
                $('#selectFileModel').modal('show');
                return false;
            }

            $("#updating").show();
            $("#results").hide();
        });
        $("a.advanced").click(function() {
            if ($("#advanced-state").hasClass("glyphicon-menu-right")) {
                $("#advanced-state").removeClass("glyphicon-menu-right");
                $("#advanced-state").addClass("glyphicon-menu-down");
            } else {
                $("#advanced-state").removeClass("glyphicon-menu-down");
                $("#advanced-state").addClass("glyphicon-menu-right");
            }
        });
        $("#submitStats").click(function() {
            $("#submitLoading").show();
            $("textarea[name=comment]").hide();
            $("#submitStats").attr("disabled", true);
            $.ajax({
                type: "POST",
                url: "{{ url('/statsreports/' . (isset($results) && $results['sheets'] ? $results['sheets'][0]['statsReportId'] : 0) . '/submit') }}",
                data: "dataType=JSON&function=submit&comment=" + encodeURIComponent($("textarea[name=comment]").val()),
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                success: function(response) {
                    $("#submitResult span.message").html(response.message);
                    if (response.success) {
                        $("#submitResult").removeClass("alert-danger");
                        $("#submitResult").addClass("alert-success");
                    } else {
                        $("#submitResult").removeClass("alert-success");
                        $("#submitResult").addClass("alert-danger");
                    }
                    $("#submitTime").text(response.submittedAt);
                    $("#submitResult").show();
                    $('#submitModel').modal('hide');
                    $('#submitCompleteModel').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var code = jqXHR.status;

                    var message = '';
                    if (code == 404) {
                        message = 'We were unable to find that report. Please try validating and submitting your report again.';
                    } else if (code == 403) {
                        message = 'You are not allowed to submit this report.';
                    } else {
                        message = 'There was a problem submitting your report. Please try again.';
                    }

                    $("#submitResult span.message").html('<p>' + message + '</p>');
                    $("#submitResult").removeClass("alert-success");
                    $("#submitResult").addClass("alert-danger");

                    $("#submitResult").show();
                    $('#successMessage').hide();
                    $('#submitModel').modal('hide');
                    $('#submitCompleteModel').modal('show');
                }
            });
        });
        $("#submitStatsCancel").click(function() {
            $("textarea[name=comment]").show();
            $("#submitLoading").hide();
            $("#submitStats").attr("disabled", false);
        });
        $("#submitteOk").click(function() {
            window.location.replace("{{ (isset($results) && $results['sheets'] && $results['sheets'][0]['statsReportId']) ? url("statsreports/{$results['sheets'][0]['statsReportId']}") : url('/') }}");
        });
    });
</script>
@stop

