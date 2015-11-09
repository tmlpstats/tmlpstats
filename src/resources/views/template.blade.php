<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TMLP Stats</title>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script type="text/javascript" src="{{ asset('/js/jquery-2.1.4.min.js') }}"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="{{ asset('/js/bootstrap.min.js') }}"></script>

    <!-- Help with Timezones -->
    <script type="text/javascript" src="{{ asset('/js/jstz.min.js') }}"></script>

    <!-- DataTables -->
    <script type="text/javascript" src="{{ asset('/js/query.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/dataTables.bootstrap.js') }}"></script>

    <!-- Bootstrap -->
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/bootstrap.vertical-tabs.min.css') }}" rel="stylesheet">

    <!-- Font-Awesome -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript" src="{{ asset('/js/jquery.loading.js') }}"></script>
    <link href="{{ asset('/css/jquery.loading.css') }}" rel="stylesheet">

    <link href="{{ asset('/css/tmlpstats.css') }}" rel="stylesheet">

    <style type="text/css">
        body {
            padding-top: 50px;
        }
    </style>
    @yield('headers')
</head>
<body>
    @include('partials.navbar')

    <div class="container">
        @yield('content')
    </div>

    @yield('scripts')

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
                            location.reload();
                        }
                    });
                }
            });
        </script>
    @endif
</body>
</html>
