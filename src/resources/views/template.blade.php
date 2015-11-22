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

    <script src="{{ asset('/components/bootstrap/dist/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jquery-loading/dist/jquery.loading.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/components/jstz/jstz.min.js') }}" type="text/javascript"></script>

    @if (!Session::has('timezone') || !Session::has('locale'))
        <script type="text/javascript">
            $(document).ready(function () {
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
            });
        </script>
    @endif

    @yield('scripts')

</body>
</html>
