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
    <link href="{{ asset('/components/jquery-loading/dist/jquery.loading.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/components/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <link href="{{ asset('/css/tmlpstats.css') }}" rel="stylesheet">

    @yield('headers')
</head>
<body>
    @if (!isset($skip_navbar) || $skip_navbar == false)
        @include('partials.navbar')
    @endif
    <div class="container-fluid">
        @yield('content')
    </div>

    @if (Auth::check())
        <div class="hs">
            <button id="contactLink" href="#" title="Feedback">
                <div id="feedbackTabText">Feedback</div>
            </button>
        </div>

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
    @endif

    <script src="{{ asset('/components/bootstrap/dist/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jquery-loading/dist/jquery.loading.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jquery-stickytabs/jquery.stickytabs.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/moment/min/moment-with-locales.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/highcharts/highcharts.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jstz/jstz.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/tmlpstats.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            @if (Auth::check())
                Tmlp.enableFeedback({
                    firstName: '{{ Auth::user() ? Auth::user()->firstName : '' }}',
                    email: '{{ Auth::user() ? Auth::user()->email : '' }}',
                    feedbackUrl: '{{ url('/feedback') }}',
                    csrfToken: '{{ csrf_token() }}'
                });
            @endif

            @if (!Session::has('timezone') || !Session::has('locale'))
                Tmlp.setTimezone({
                    clientSettingsUrl: '{{ url('/home/clientsettings') }}',
                    csrfToken: '{{ csrf_token() }}',
                    isHome: <?= Request::is('/home')? 'true': 'false' ?>
                });
            @endif
        });
    </script>

    @yield('scripts')

</body>
</html>
