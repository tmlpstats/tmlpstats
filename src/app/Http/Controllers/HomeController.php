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
use TmlpStats\Import\Xlsx\XlsxArchiver;
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
        if (Auth::user()->hasRole('localStatistician')) {
            $centerAbbr = strtolower(Auth::user()->center->abbreviation);
            return redirect("center/{$centerAbbr}");
        } else {
            $region = $this->getRegion($request);
            if ($region != null) {
                return redirect(action('HomeController@home', ['abbr' => strtolower($region->abbreviation)]));
            }
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
        App::make(Api\Context::class)->setRegion($region);

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

        $reportingDate = $this->getReportingDate($request, array_keys($reportingDates));

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
                $regionsData[0]['displayName'] = 'India Region';
                $regionsData[0]['validatedCount'] = 0;
                $regionsData[0]['completeCount'] = 0;
                $regionsData[0]['centersData'] = array();
                break;
            case 'NA':
            default:
                $regionsData['East']['displayName'] = 'North America - Eastern Region';
                $regionsData['East']['validatedCount'] = 0;
                $regionsData['East']['completeCount'] = 0;
                $regionsData['East']['centersData'] = array();

                $regionsData['West']['displayName'] = 'North America - Western Region';
                $regionsData['West']['validatedCount'] = 0;
                $regionsData['West']['completeCount'] = 0;
                $regionsData['West']['centersData'] = array();
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

            $sheetUrl = null;
            $reportUrl = null;

            if (Gate::allows('downloadSheet', $statsReport)) {
                $sheetUrl = $statsReport && XlsxArchiver::getInstance()->getSheetPath($statsReport)
                ? url("/statsreports/{$statsReport->id}/download")
                : null;
                $reportUrl = $statsReport
                ? StatsReportController::getUrl($statsReport)
                : null;
            }

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
                'rating' => $statsReport && $statsReport->isValidated() ? $statsReport->getRating() . " (" . $statsReport->getPoints() . " pts)" : '-',
                'updatedAt' => $submittedAt ? $submittedAt->format('M j, Y @ g:ia T') : '-',
                'updatedBy' => $user ? $user->firstName : '-',
                'sheet' => $sheetUrl,
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
