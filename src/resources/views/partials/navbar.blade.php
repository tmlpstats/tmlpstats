<?php
$homeUrl = Session::get('homePath', '/');

$regions = TmlpStats\Region::isGlobal()->get();
$currentRegion = App::make(TmlpStats\Http\Controllers\Controller::class)
                    ->getRegion(Request::instance());

$centers = TmlpStats\Center::byRegion($currentRegion)->orderBy('name')->get();
$currentCenter = App::make(TmlpStats\Http\Controllers\Controller::class)
                    ->getCenter(Request::instance());

$reportingDate = App::make(TmlpStats\Http\Controllers\Controller::class)
                    ->getReportingDate(Request::instance());
$quarter = TmlpStats\Quarter::getQuarterByDate($reportingDate, $currentRegion);

$reports = TmlpStats\GlobalReport::between($quarter->startWeekendDate, $quarter->endWeekendDate)
                                 ->orderBy('reporting_date', 'desc')
                                 ->get();

$showNavCenterSelect = isset($showNavCenterSelect) ? $showNavCenterSelect : false;
?>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url($homeUrl) }}">TMLP Stats</a>
        </div>

        <div id="navbar" class="collapse navbar-collapse">
            @if (Auth::check())
                <div class="navbar-left">
                    <ul class="nav navbar-nav">
                        {{-- Validate Stats --}}
                        @can ('validate', TmlpStats\StatsReport::class)
                        <li {!! Request::is('validate') ? 'class="active"' : '' !!}>
                            <a href="{{ url('validate') }}">Validate</a>
                        </li>
                        @endcan

                        {{-- Admin --}}
                        @if (Auth::user()->hasRole('administrator'))
                            <li class="dropdown {{ Request::is('admin') || Request::is('admin/*') ? 'active' : '' }}">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false">
                                    Admin <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ url('/admin/users') }}">Users</a></li>
                                    <li><a href="{{ url('/users/invites') }}">Invites</a></li>
                                    <li><a href="{{ url('/admin/centers') }}">Centers</a></li>
                                    <li><a href="{{ url('/regions') }}">Regions</a></li>
                                    <li><a href="{{ url('/globalreports') }}">Global Reports</a></li>
                                    <li><a href="{{ url('/import') }}">Import Sheets</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    @if (Auth::check())
                        {{-- Reporting Date --}}
                        <li class="dropdown">
                            <a href="#" class="btn btn-default btn-outline btn-circular navbar-btn dropdown-toggle"
                               data-toggle="dropdown" role="button" aria-expanded="false">
                                @if ($reportingDate)
                                    {{ $reportingDate->format('M j, Y')}}
                                @else
                                    Report <span class="caret"></span>
                                @endif
                            </a>
                            <ul id="reportSelect" class="dropdown-menu" role="menu">
                                @foreach ($reports as $report)
                                    <li class="menu-option" data-url="{{ url("/reports/dates/setActive") }}"
                                        data-value="{{ $report->reportingDate->toDateString() }}">
                                        <a href="#">
                                            @if ($reportingDate && $report->reportingDate->eq($reportingDate))
                                                <span class="glyphicon glyphicon-ok"></span>
                                            @else
                                                <span class="glyphicon">&nbsp;</span>
                                            @endif
                                            {{ $report->reportingDate->format('M j, Y')}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                        {{-- Region/Center toggle --}}
                        <li class="dropdown">
                            <?php
                            $url = Request::is('reports/regions/*')
                                ? url('/reports/centers')
                                : url('/reports/regions');
                            ?>
                            <a href="{{ $url }}" class="btn btn-primary navbar-btn btn-circular btn-toggle"
                               role="button">
                                @if (Request::is('reports/regions/*'))
                                    Center Report
                                @else
                                    Regional Report
                                @endif
                            </a>
                        </li>

                        {{-- Center --}}
                        @if ($showNavCenterSelect && (Auth::user()->isAdmin() || Auth::user()->hasRole('globalStatistician')))
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false">
                                    Center <span class="caret"></span>
                                </a>
                                <ul id="centerSelect" class="dropdown-menu" role="menu">
                                    @foreach ($centers as $center)
                                        <li class="menu-option"
                                            data-url="{{ url("/reports/centers/{$center->abbreviation}") }}?reportRedirect=center"
                                            data-value="{{ $center->id }}">
                                            <a href="#">
                                                @if ($currentCenter && $center->id == $currentCenter->id)
                                                    <span class="glyphicon glyphicon-ok"></span>
                                                @else
                                                    <span class="glyphicon">&nbsp;</span>
                                                @endif
                                                {{ $center->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif

                        {{-- Region --}}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">
                                Region <span class="caret"></span>
                            </a>
                            <ul id="regionSelect" class="dropdown-menu" role="menu">
                                @foreach ($regions as $region)
                                    <li class="menu-option"
                                        data-url="{{ url("/reports/regions/{$region->abbreviation}") }}?reportRedirect=region"
                                        data-value="{{ $region->id }}">
                                        <a href="#">
                                            @if ($currentRegion && $region->id == $currentRegion->id)
                                                <span class="glyphicon glyphicon-ok"></span>
                                            @else
                                                <span class="glyphicon">&nbsp;</span>
                                            @endif
                                            {{ $region->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                    {{-- Login/Profile --}}
                    @if (Auth::guest() || Auth::user()->hasRole('readonly')))
                    <li {!! Request::is('/') ? 'class="active"' : '' !!}>
                        <a href="{{ url('/auth/login') }}">Login</a>
                    </li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">{{ Auth::user()->firstName }} <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/auth/logout') }}">Logout</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</nav>

<script type="text/javascript">
    // Enable hover dropdowns in nav menu
    $(function () {
        $(".dropdown").hover(
            function () {
                $('.dropdown-menu', this).stop(true, true).fadeIn("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");
            },
            function () {
                $('.dropdown-menu', this).stop(true, true).fadeOut("fast");
                $(this).toggleClass('open');
                $('b', this).toggleClass("caret caret-up");
            }
        );

        $("#reportSelect").on("click", "li.menu-option", function (e) {
            var url = $(this).attr('data-url');
            var data = {};
            data.date = $(this).attr('data-value');

            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                data: $.param(data),
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });

        $("#regionSelect").on("click", "li.menu-option", function (e) {
                @if (Request::is('reports/*'))
            var url = $(this).attr('data-url');
            window.location.replace(url);
                @else
            var data = {};
            data.id = $(this).attr('data-value');
            $.ajax({
                type: "POST",
                url: "{{ url("/reports/regions/setActive") }}",
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                data: $.param(data),
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
            @endif
        });

        $("#centerSelect").on("click", "li.menu-option", function (e) {
                @if (Request::is('reports/*'))
            var url = $(this).attr('data-url');
            window.location.replace(url);
                @else
            var data = {};
            data.id = $(this).attr('data-value');
            $.ajax({
                type: "POST",
                url: "{{ url("/reports/centers/setActive") }}",
                beforeSend: function (request) {
                    request.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");
                },
                data: $.param(data),
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
            @endif
        });
    });
</script>
