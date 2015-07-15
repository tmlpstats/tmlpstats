<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Import\ImportManager;
use TmlpStats\Center;
use TmlpStats\User;
use TmlpStats\StatsReport;
use TmlpStats\CenterStatsData;
use TmlpStats\Quarter;

use Carbon\Carbon;

use Auth;
use Session;
use Request;
use Input;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$timezone = '';
		if (Session::has('timezone')) {
			$timezone =  Session::get('timezone');
		}

		$userHomeRegion = Auth::user()->homeRegion();
		$defaultRegion = $userHomeRegion ?: 'NA';

		$region = Request::has('region') ? Request::get('region') : $defaultRegion;

		$allReports = StatsReport::currentQuarter($region)->orderBy('reporting_date', 'desc')->get();
		if ($allReports->isEmpty()) {
			$allReports = StatsReport::lastQuarter($region)->orderBy('reporting_date', 'desc')->get();
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

        $reportingDate = null;
        $reportingDateString = Input::has('stats_report')
            ? Input::get('stats_report')
            : '';

        if ($reportingDateString && isset($reportingDates[$reportingDateString])) {
        	$reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDateString);
        } else if ($today->dayOfWeek == Carbon::FRIDAY) {
        	$reportingDate = $today;
        } else if (!$reportingDate && $reportingDates) {
        	$reportingDate = $allReports[0]->reportingDate;
        } else {
        	$reportingDate = ImportManager::getExpectedReportDate();
        }

		$centers = Center::active()
						 ->globalRegion($region)
						 ->orderBy('local_region', 'asc')
						 ->orderBy('name', 'asc')
						 ->get();

		$regionsData = array();

		switch($region) {
			case 'ANZ':
				$regionsData[0]['displayName']    = 'Australia/New Zealand Region';
				$regionsData[0]['validatedCount'] = 0;
				$regionsData[0]['completeCount']  = 0;
				$regionsData[0]['centersData']    = array();
				break;
			case 'EME':
				$regionsData[0]['displayName']    = 'Europe/Middle East Region';
				$regionsData[0]['validatedCount'] = 0;
				$regionsData[0]['completeCount']  = 0;
				$regionsData[0]['centersData']    = array();
				break;
			case 'IND':
				$regionsData[0]['displayName']    = 'India Region';
				$regionsData[0]['validatedCount'] = 0;
				$regionsData[0]['completeCount']  = 0;
				$regionsData[0]['centersData']    = array();
				break;
			case 'NA':
			default:
				$regionsData['East']['displayName']    = 'North America - Eastern Region';
				$regionsData['East']['validatedCount'] = 0;
				$regionsData['East']['completeCount']  = 0;
				$regionsData['East']['centersData']    = array();

				$regionsData['West']['displayName']    = 'North America - Western Region';
				$regionsData['West']['validatedCount'] = 0;
				$regionsData['West']['completeCount']  = 0;
				$regionsData['West']['centersData']    = array();
				break;
		}

		foreach ($centers as $center) {

			$localRegion = $center->localRegion ?: 0;

			$statsReport = $center->statsReports()
								  ->reportingDate($reportingDate->toDateString())
								  ->orderBy('submitted_at', 'desc')
								  ->first();

			$user = $statsReport
				? User::find($statsReport->user_id)
				: null;

			$actualData = $statsReport
				? CenterStatsData::actual()->reportingDate($reportingDate->toDateString())->statsReport($statsReport)->first()
				: null;

			$sheetUrl = null;

			if (Auth::user()->hasRole('globalStatistician') || Auth::user()->hasRole('administrator')
				|| (Auth::user()->hasRole('localStatistician') && Auth::user()->hasCenter($center->id))
			) {
				$sheetUrl = ImportManager::getSheetPath($reportingDate->toDateString(), $center->sheetFilename)
					? route('downloadSheet', array($reportingDate->toDateString(), $center->sheetFilename))
					: null;
			}

			$updatedAt = $statsReport
				? Carbon::createFromFormat('Y-m-d H:i:s', $statsReport->updatedAt, 'UTC')
				: null;

			if ($updatedAt) {
				$localTimezone = $timezone ?: 'America/Los_Angeles';
				$updatedAt->setTimezone($localTimezone);
			}

			$centerResults = array(
				'name'        => $center->name,
				'localRegion' => $center->localRegion,
				'submitted'   => $statsReport ? $statsReport->isSubmitted() : false,
				'validated'   => $statsReport ? $statsReport->isValidated() : false,
				'rating'      => $actualData ? $actualData->rating : '-',
				'updatedAt'   => $updatedAt ? $updatedAt->format('M j, Y @ g:ia T') : '-',
				'updatedBy'   => $user ? $user->firstName : '-',
				'sheet'       => $sheetUrl,
			);

			if ($statsReport && $statsReport->validated) {
				$regionsData[$localRegion]['completeCount'] += 1;
			}
			$regionsData[$localRegion]['validatedCount'] += 1;

			$regionsData[$localRegion]['centersData'][] = $centerResults;
		}

		foreach ($regionsData as &$sortRegion) {
			usort($sortRegion['centersData'], array(get_class(), 'sortByComplete'));
		}

		return view('home')->with(['reportingDate'  => $reportingDate,
								   'reportingDates' => $reportingDates,
								   'timezone'	    => $timezone,
								   'selectedRegion' => $region,
								   'regionsData'	=> $regionsData]);
	}

	protected static function sortByComplete($a, $b)
	{
		if ($a['validated'] != $b['validated']) {
			return $a['validated'] ? -1 : 1; // reverse order to get sort in DESC order
		} else {
			return strcmp($a['name'], $b['name']);
		}
	}

	public function setTimezone()
	{
		if (Request::has('timezone')) {
			Session::put('timezone', Request::get('timezone'));
		}
	}
}
