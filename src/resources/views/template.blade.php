<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TMLP Stats</title>

    <script type="text/javascript" src="{{ asset('/components/jquery/dist/jquery.min.js') }}"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ asset('/components/html5shiv/dist/html5shiv.min.js') }}"></script>
    <script src="{{ asset('/components/response/dist/respond.min.js') }}"></script>
    <![endif]-->

    <link href="{{ asset('/components/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/components/datatables/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/components/bootstrap-vertical-tabs/bootstrap.vertical-tabs.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/components/jquery-loading/dist/jquery.loading.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/components/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <link href="{{ asset('/css/tmlpstats.css') }}" rel="stylesheet">

    @yield('headers')
</head>
<body>
    @include('partials.navbar')

    <div class="container">
        @yield('content')
    </div>

    <button id="contactLink" href="#" title="Feedback">
        <div id="feedbackTabText">Feedback</div>
    </button>

    <div class="modal fade" id="feedbackModel" tabindex="-1" role="dialog" aria-labelledby="feedbackModelLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="feedbackModelLabel">Send us your feedback</h4>
                </div>
                <div class="modal-body">
                    We would love to hear your feedback! If you have any comments, ideas, suggestions, issues, or anything else, please let us know below.
                    <br/><br/>
                    Also, if you or a TMLP member/graduate you know are interested in participating in creating a new future of statistics for TMLP, let us know that too!
                    <br/><br/>
                    <div id="feedbackSubmitResult" class="alert" role="alert" style="display:none">
                        <span class="message"></span>
                    </div>
                    <div id="feedbackForm">
                        {!! Form::open(['url' => url('/feedback')]) !!}
                        <br/>
                        {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                        <br/>
                        {!! Form::label('email', 'Email:', ['class' => 'control-label']) !!}
                        {!! Form::email('email', null, ['class' => 'form-control']) !!}
                        <br/>
                        {!! Form::label('message', 'Message:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => '10']) !!}
                        <br/>
                        {!! Form::checkbox('copySender', 1, true, []) !!} Send me a copy

                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="submitFeedbackCancel">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitFeedback">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/components/bootstrap/dist/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jquery-loading/dist/jquery.loading.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jquery-stickytabs/jquery.stickytabs.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/moment/min/moment-with-locales.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jstz/jstz.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/tmlpstats.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        function resetFeedbackForm() {

            $("#feedbackSubmitResult").hide();
            $("#feedbackForm").show();

            $("#submitFeedback").attr("disabled", false);
            $("#submitFeedback").show();

            $("#submitFeedbackCancel").val('Cancel');


            $("input[name=name]").val("{{ Auth::user() ? Auth::user()->firstName : '' }}");
            $("input[name=email]").val("{{ Auth::user() ? Auth::user()->email : '' }}");
            $("textarea[name=message]").val("");
            $("input[name=copySender]").prop('checked', true);

            feedbackFormDirty = false;
        }

        var feedbackFormDirty = false;

        $(document).ready(function () {

            resetFeedbackForm();

            $("#contactLink").on('click', function() {

                if (feedbackFormDirty) {
                    resetFeedbackForm();
                }

                $('#feedbackModel').modal('show');
            });

            $("#submitFeedback").on('click', function() {

                $("#submitFeedback").attr("disabled", true);

                var data = {};
                data.dataType = 'JSON';
                data.name = $("input[name=name]").val();
                data.email = $("input[name=email]").val();
                data.message = $("textarea[name=message]").val();

                var copySender = $("input[name=copySender]").val();
                if (copySender) {
                    data.copySender = copySender;
                }

                feedbackFormDirty = true;

                $.ajax({
                    type: "POST",
                    url: "{{ url('/feedback') }}",
                    data: $.param(data),
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                    },
                    success: function(response) {
                        var $resultDiv = $("#feedbackSubmitResult");
                        $resultDiv.find("span.message").html(response.message);
                        if (response.success) {
                            $resultDiv.removeClass("alert-danger");
                            $resultDiv.addClass("alert-success");
                        } else {
                            $resultDiv.removeClass("alert-success");
                            $resultDiv.addClass("alert-danger");
                        }
                        $resultDiv.show();

                        $("#feedbackForm").hide();
                        $("#submitFeedback").hide();
                        $("#submitFeedbackCancel").html('Close');
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

                        var $resultDiv = $("#feedbackSubmitResult");
                        $resultDiv.find("span.message").html('<p>' + message + '</p>');
                        $resultDiv.removeClass("alert-success");
                        $resultDiv.addClass("alert-danger");
                        $resultDiv.show();

                        $("#submitFeedback").attr("disabled", false);
                    }
                });
            });

            @if (!Session::has('timezone') || !Session::has('locale'))
                var tz = jstz.determine();
                var locale = navigator.language;

                var data = {};
                if (typeof (tz) !== 'undefined') {
                    data.timezone = tz.name();
                }
                if (locale) {
                    data.locale = navigator.language;
                }

                if (!$.isEmptyObject(data)) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('/home/clientsettings') }}",
                        beforeSend: function (request) {
                            request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                        },
                        data: $.param(data),
                        success: function () {
                            @if (Request::is('/home'))
                                location.reload();
                            @endif
                        }
                    });
                }
            @endif
        });
    </script>

    @yield('scripts')

</body>
</html>
