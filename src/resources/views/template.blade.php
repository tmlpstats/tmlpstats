<?php
if (!isset($skip_navbar)) {
    $skip_navbar = false;
}

$center = App::make(TmlpStats\Http\Controllers\Controller::class)->getCenter(Request::instance());
$region = App::make(TmlpStats\Http\Controllers\Controller::class)->getRegion(Request::instance());
$reportingDate = App::make(TmlpStats\Http\Controllers\Controller::class)->getReportingDate();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TMLP Stats</title>

    <script type="text/javascript" src="{{ asset('vendor/js/jquery.min.js') }}"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ asset('vendor/js/html5shiv/dist/html5shiv.min.js') }}"></script>
    <script src="{{ asset('vendor/js/response/dist/respond.min.js') }}"></script>
    <![endif]-->

    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
    <link href="{{ elixir('css/main.css') }}" rel="stylesheet">

    @yield('headers')
</head>
<body <?php if($skip_navbar) { ?>class="no-navbar"<?php } ?>>
    @if (!$skip_navbar)
        @include('partials.navbar')
    @endif
    <div id="main-container" class="container-fluid">
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

    @include('partials.settings')

    <script src="{{ elixir('js/tmlp-polyfill.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/api.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/tmlpstats.js') }}" type="text/javascript"></script>
    <script src="{{ elixir('js/main.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            @if (Auth::check())
                Tmlp.enableFeedback({
                    firstName: @json(Auth::user() ? Auth::user()->firstName : ''),
                    email: @json(Auth::user() ? Auth::user()->email : ''),
                    feedbackUrl: @json(url('/feedback')),
                    csrfToken: @json(csrf_token())
                });
                @if (!Session::has('timezone') && !Session::has('locale'))
                    Tmlp.setTimezone({
                        isHome: @json(Request::is('/home'))
                    });
                @endif
            @endif
        });
    </script>

    @yield('scripts')

</body>
</html>
