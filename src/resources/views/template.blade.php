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
        @include('partials.feedback')
    @endif

    @include('partials.settings')

    <script src="{{ elixir('js/tmlp-polyfill.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/commons.js') }}" type="text/javascript"></script>
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
