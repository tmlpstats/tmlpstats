<?php
    $homeUrl = Session::get('homePath', '/');
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
            <ul class="nav navbar-nav">
                @if (Auth::check())
                    <li {{ Request::is($homeUrl) ? 'class="active"' : '' }}>
                        <a href="{{ url($homeUrl) }}">Home</a>
                    </li>
                    @can ('validate', TmlpStats\StatsReport::class)
                        <li {!! Request::is('import') ? 'class="active"' : '' !!}>
                            <a href="{{ url('/import') }}">Validate Stats</a>
                        </li>
                    @endcan
                    @if (Auth::user()->hasRole('administrator'))
                        <li class="dropdown {{ Request::is('admin') || Request::is('admin/*') ? 'active' : '' }}">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                Admin <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/admin/users') }}">Users</a></li>
                                <li><a href="{{ url('/admin/centers') }}">Centers</a></li>
                                <li><a href="{{ url('/globalreports') }}">Global Reports</a></li>
                                <li><a href="{{ url('/admin/import') }}">Import Sheets</a></li>
                            </ul>
                        </li>
                    @endif
                @endif
            </ul>

            <ul class="nav navbar-nav navbar-right">
                @if (Auth::guest() || (Auth::user()->hasRole('readonly') && Request::is('home')))
                    <li {!! Request::is('/') ? 'class="active"' : '' !!}>
                        <a href="{{ url('/auth/login') }}">Login/Register</a>
                    </li>
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->firstName }} <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            @if (Auth::user()->hasRole('readonly'))
                                <li><a href="{{ url('/auth/reauth') }}">Login</a></li>
                            @else
                                <li><a href="{{ url('/auth/logout') }}">Logout</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
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
    });
</script>
