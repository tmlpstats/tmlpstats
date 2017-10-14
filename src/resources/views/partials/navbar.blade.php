@inject('context', 'TmlpStats\Api\Context')
<?php
$currentUser = $context->getUser();
$homeRegion = $currentUser ? $currentUser->homeRegion(true) : null;
$homeUrl = Session::get('homePath', '/');
if (!isset($regionSelectAction)) {
    $regionSelectAction = 'ReportsController@getRegionReport';
}

$dateSelectAction = $context->dateSelectAction('foo');
$reportingDate = $context->getReportingDate();
$regions = TmlpStats\Region::isGlobal()->get();
$currentRegion = $context->getGlobalRegion(false);
$currentCenter = $context->getCenter(true);

// This has to be a separate variable because the submission reporting date and the reports reporting date use
// different logic to determine the best value. Submission needs to default to the next week
$submissionReportingDate = $context->getSubmissionReportingDate();

$crd = null;
if ($currentCenter !== null && $reportingDate !== null) {
    $crd = TmlpStats\Encapsulations\CenterReportingDate::ensure($currentCenter, $reportingDate);
}

$reports = null;
$centers = [];
if ($currentRegion !== null) {
    $quarter = $crd? $crd->getQuarter() : TmlpStats\Quarter::getQuarterByDate($reportingDate, $currentRegion);

    // Add a week before and after so we can switch between quarters
    $startDate = $quarter->getQuarterStartDate($currentCenter);
    $endDate = $quarter->getQuarterEndDate($currentCenter)->addWeek();

    $reports = TmlpStats\GlobalReport::between($startDate, $endDate)
        ->orderBy('reporting_date', 'desc')
        ->get();
    $centers = TmlpStats\Center::byRegion($currentRegion)->orderBy('name')->get();
}

$reportingDateString = ($reportingDate != null) ? $reportingDate->toDateString() : null;
$showNavCenterSelect = isset($showNavCenterSelect) ? $showNavCenterSelect : false;
?>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
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

                        @can ('validate', TmlpStats\StatsReport::class)
                            @can ('showNewSubmissionUi', $currentCenter)
                            <li {!! Request::is('center/*/submission/*') ? 'class="active"' : '' !!}>
                                <a href="{{ action('CenterController@submission', ['abbr' => $currentCenter->abbrLower(), 'reportingDate' => $submissionReportingDate->toDateString()]) }}">Submit Report</a>
                            </li>
                            @else
                            <li {!! Request::is('validate') ? 'class="active"' : '' !!}>
                                <a href="{{ route('validate') }}">Validate</a>
                            </li>
                            @endcan
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
                                    @if ($homeRegion)
                                    <li><a href="{{ url("/admin/regions/{$homeRegion->abbrLower()}") }}">Regions</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    @if (Auth::check() && $reports)
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
                            @if ($dateSelectAction)
                                <ul id="reportSelect" class="dropdown-menu" role="menu">
                                    @foreach ($reports as $report)
                                        <li class="menu-option">
                                            <a href="{{ $context->dateSelectAction($report->reportingDate) }}">{{ $report->reportingDate->format('M j, Y')}}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <ul id="reportSelect" class="dropdown-menu ajax-report-select" role="menu">
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
                            @endif
                        </li>

                        {{-- Region report button shows when you're not in a regional report --}}
                        @if ($currentRegion != null && $currentUser->userCan('showReportButton'))
                        <li class="dropdown">
                            <a href="{{ $currentRegion->getUriRegionReport($reportingDate) }}" class="btn btn-primary navbar-btn btn-circular btn-toggle"
                               role="button">
                                Regional Report
                            </a>
                        </li>
                        @endcan

                        {{-- Center --}}
                        @if ($currentUser->isAdmin() || $currentUser->hasRole('globalStatistician'))
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false">
                                    Center <span class="caret"></span>
                                </a>
                                <ul id="centerSelect" class="dropdown-menu" role="menu">
                                    @foreach ($centers as $center)
                                        <li class="menu-option"
                                            data-value="{{ $center->id }}">
                                            <a href="{{ $center->getUriCenterReport($reportingDate) }}">
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
                                    <li class="menu-option">
                                        <a href="{{ action($regionSelectAction, ['abbr' => $region->abbrLower(), 'date' => $reportingDateString]) }}">
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
    @if (config('app.env') == 'stage')
    <div class="stage-warning">This is NOT the official site. Use <a href="https://tmlpstats.com">tmlpstats.com</a></div>
    @endif
</nav>
