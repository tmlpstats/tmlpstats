@extends('template')

@section('content')
    <h1>Import Stats Sheet</h1>

    {!! Form::open(['url' => '/import', 'files' => true]) !!}
    <div class="form-group">
        {!! Form::label('statsFiles[]', 'Center Stats File:') !!}
        {!! Form::file('statsFiles[]', [
                'class'=> 'file form-control',
                'multiple' => true,
                'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel.sheet.macroEnabled.12'
            ])
        !!}
    </div>

    <div class="alert alert-warning" role="alert">
        <strong>Notice:</strong> By clicking Import, this selected sheet will be submitted and included in the global
        report as is. No emails will be sent.
    </div>


    {!! Form::submit('Import', ['id' => 'validate', 'class' => 'btn btn-primary']) !!}

    <div id="updating" style="display:none">
        <br/>

        <p>Please be patient. <span style="font-weight:bold">It may take up to 1 minute to complete.</span>

        @include('partials.loading')
    </div>

    <div class="modal fade" id="selectFileModel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
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

    {!! Form::hidden('ignoreReportDate', true) !!}
    {!! Form::hidden('ignoreVersion', true) !!}
    {!! Form::hidden('submitReport', false) !!}
    {!! Form::close() !!}

    @if (isset($results))
        @include('import.upload', ['results' => $results])
    @endif

    <script type="text/javascript">
        $(function ($) {
            $("input[name='ignoreReportDate']").click(function () {
                if ($(this).is(':checked')) {
                    $('#expectedReportDate').attr("disabled", true);
                } else if ($(this).not(':checked')) {
                    $('#expectedReportDate').removeAttr("disabled");
                }
            });
            $("#validate").click(function () {

                if (!$("input[type=file]").val()) {
                    $('#selectFileModel').modal('show');
                    return false;
                }

                $("#updating").show();
                $("#results").hide();
            });
            $("a.advanced").click(function () {
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
@endsection
