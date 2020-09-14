<?php
namespace TmlpStats\Http\Controllers;

use App;
use Auth;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Session;
use TmlpStats\Api;
use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Region;
use TmlpStats\StatsReport;
use TmlpStats\User;

class HomeController extends Controller
{
    /**
     * Show the application dashboard to the user.
     *
     * Local statisticians are sent to their center's dashboard
     * Regional statisticians and admins go to the reginal overview dashboard
     *
     * @return \View
     */
    public function index(Request $request)
    {
        $user = $this->context->getUser();
        $reportingDate = $this->context->getReportingDate();

        // Statisticians default to their center's most recent report
        if ($user->hasRole('localStatistician')) {
            return redirect(action('ReportsController@getCenterReport', [
                'abbr' => $user->center->abbrLower(),
                'date' => $reportingDate->toDateString(),
            ]));
        }

        // Program Leaders default to their region's most recent report
        if ($user->hasRole('programLeader')) {
            return redirect(action('ReportsController@getRegionReport', [
                'abbr' => $user->center->getGlobalRegion()->abbrLower(),
                'date' => $reportingDate->toDateString(),
            ]));
        }

        // Admins/global statisticians default to the region overview page
        $region = $this->context->getRegion(true);
        if ($region) {
            return redirect(action('HomeController@home', [
                'abbr' => $region->abbrLower(),
            ]));
        }
    }

    public function home(Request $request, $abbr)
    {
        $region = Region::abbreviation($abbr)->firstorFail();

        return $this->regionOverview($request, $region);
    }

    /**
     * Show the regional overview dashboard.
     *
     * @param Request $request
     *
     * @return \View
     */
    public function regionOverview(Request $request, Region $region)
    {
        $context = App::make(Api\Context::class);
        $context->setRegion($region);
        $context->setDateSelectAction('ReportsController@getRegionReport', [
            'abbr' => $region->abbrLower(),
            'tab1' => 'WeeklySummaryGroup',
            'tab2' => 'RatingSummary',
        ]);

        if (Gate::denies('index', StatsReport::class)) {
            // If they aren't allowed to see the full home page, just return a blank home page
            return view('home');
        }

        $this->authorize('index', StatsReport::class);

        $timezone = '';
        if (Session::has('timezone')) {
            $timezone = Session::get('timezone');
        }

        $allReports = StatsReport::currentQuarter($region)->submitted()->orderBy('reporting_date', 'desc')->get();
        if ($allReports->isEmpty() || $allReports->count() == 1) {
            $allReports = StatsReport::lastQuarter($region)->submitted()->orderBy('reporting_date', 'desc')->get();
        }

        $today = Carbon::now();
        $reportingDates = array();

        if ($today->dayOfWeek == Carbon::FRIDAY) {
            $reportingDates[$today->toDateString()] = $today->format('F j, Y');
        }
        foreach ($allReports as $report) {
            $dateString = $report->reportingDate->toDateString();
            $reportingDates[$dateString] = $report->reportingDate->format('F j, Y');
        }

        $reportingDate = Carbon::parse(key($reportingDates))->startOfDay();

        $centers = Center::active()
            ->byRegion($region)
            ->orderBy('name', 'asc')
            ->get();

        $regionsData = array();

        switch ($region->abbreviation) {
            case 'ANZ':
                $regionsData[0]['displayName'] = 'Australia/New Zealand Region';
                $regionsData[0]['validatedCount'] = 0;
                $regionsData[0]['completeCount'] = 0;
                $regionsData[0]['centersData'] = array();
                break;
            case 'EME':
                $regionsData[0]['displayName'] = 'Europe/Middle East Region';
                $regionsData[0]['validatedCount'] = 0;
                $regionsData[0]['completeCount'] = 0;
                $regionsData[0]['centersData'] = array();
                break;
            case 'IND':
                $regionsData['MUM']['displayName'] = 'India - Mumbai Center';
                $regionsData['MUM']['validatedCount'] = 0;
                $regionsData['MUM']['completeCount'] = 0;
                $regionsData['MUM']['centersData'] = array();

                $regionsData['BLR']['displayName'] = 'India - Bangalore Center';
                $regionsData['BLR']['validatedCount'] = 0;
                $regionsData['BLR']['completeCount'] = 0;
                $regionsData['BLR']['centersData'] = array();

                $regionsData['DL']['displayName'] = 'India - Delhi Center';
                $regionsData['DL']['validatedCount'] = 0;
                $regionsData['DL']['completeCount'] = 0;
                $regionsData['DL']['centersData'] = array();

                break;
            case 'NA':
            default:
//                $regionsData[0]['displayName'] = 'North America Region';
//                $regionsData[0]['validatedCount'] = 0;
//                $regionsData[0]['completeCount'] = 0;
//                $regionsData[0]['centersData'] = array();
                $regionsData['EAST']['displayName'] = 'North America - Eastern Region';
                $regionsData['EAST']['validatedCount'] = 0;
                $regionsData['EAST']['completeCount'] = 0;
                $regionsData['EAST']['centersData'] = array();

                $regionsData['WEST']['displayName'] = 'North America - Western Region';
                $regionsData['WEST']['validatedCount'] = 0;
                $regionsData['WEST']['completeCount'] = 0;
                $regionsData['WEST']['centersData'] = array();
                break;
        }

        foreach ($centers as $center) {

            $localRegion = $center->getLocalRegion();
            $localRegion = $localRegion ? $localRegion->abbreviation : 0;

            $statsReport = $center->statsReports()
                                  ->reportingDate($reportingDate)
                                  ->submitted()
                                  ->orderBy('submitted_at', 'desc')
                                  ->first();

            $user = $statsReport
                ? User::find($statsReport->user_id)
                : null;

            $reportUrl = $statsReport ? StatsReportController::getUrl($statsReport) : null;

            $submittedAt = null;
            if ($statsReport) {
                $submittedAt = $statsReport->submittedAt;
                $submittedAt->setTimezone($statsReport->center->timezone);
            }

            $centerResults = array(
                'name' => $center->name,
                'localRegion' => $localRegion,
                'submitted' => $statsReport ? $statsReport->isSubmitted() : false,
                'validated' => $statsReport ? $statsReport->isValidated() : false,
                'rating' => $statsReport && $statsReport->isValidated() ? $statsReport->getRating() . ' (' . $statsReport->getPoints() . ' pts)' : '-',
                'updatedAt' => $submittedAt ? $submittedAt->format('M j, Y @ g:ia T') : '-',
                'updatedBy' => $user ? $user->firstName : '-',
                'reportUrl' => $reportUrl,
            );

            if ($statsReport && $statsReport->submittedAt) {
                $regionsData[$localRegion]['completeCount'] += 1;
            }
            $regionsData[$localRegion]['validatedCount'] += 1;

            $regionsData[$localRegion]['centersData'][] = $centerResults;
        }

        foreach ($regionsData as &$sortRegion) {
            usort($sortRegion['centersData'], array(get_class(), 'sortBySubmitted'));
        }

        $selectedRegion = $region->abbreviation;
        $globalReport = GlobalReport::reportingDate($reportingDate)->first();
        $regionSelectAction = 'HomeController@home'; // Allow the region selector to do a different action

        return view('home', compact(
            'regionSelectAction',
            'reportingDate',
            'reportingDates',
            'timezone',
            'selectedRegion',
            'regionsData',
            'globalReport'
        ));
    }

    protected static function sortBySubmitted($a, $b)
    {
        if ($a['submitted'] != $b['submitted']) {
            return $a['submitted'] ? -1 : 1; // reverse order to get sort in DESC order
        } else {
            return strcmp($a['name'], $b['name']);
        }
    }
}
