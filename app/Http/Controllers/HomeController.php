<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Center;
use TmlpStats\User;
use TmlpStats\StatsReport;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

use Session;
use Request;

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
			$timezone = 'set';
			date_default_timezone_set(Session::get('timezone'));
		} else {
			date_default_timezone_set('America/Los_Angeles');
		}

		$reportingDate = '';
		if (Request::has('stats_report')) {
			$statsReport = StatsReport::reportingDate(Request::get('stats_report'))->first();
			if ($statsReport) {
				$reportingDate = $statsReport->reportingDate;
			}
		}

		if (!$reportingDate) {
			$reportingDate = $this->getExpectedReportDate();
		}


		$allReports = StatsReport::currentQuarter()->orderBy('reporting_date', 'desc')->get();

		$reportingDates = array();
		foreach ($allReports as $report) {
			$dateString = $report->reportingDate->toDateString();
			$displayString = $report->reportingDate->format('M j, Y');

			$reportingDates[$dateString] = $displayString;
		}

		$centers = Center::active()->orderBy('local_region', 'asc')->orderBy('name', 'asc')->get();

		$centerData = array(
			'East' => array(
				'validated' => array(),
				'not-validated' => array(),
			),
			'West' => array(
				'validated' => array(),
				'not-validated' => array(),
			),
		);
		foreach ($centers as $center) {

			$statsReport = $center->statsReports()->reportingDate($reportingDate->toDateString())->first();

			$user = $statsReport
				? User::find($statsReport->user_id)
				: null;

			$actualData = $statsReport
				? CenterStatsData::actual()->reportingDate($reportingDate->toDateString())->statsReport($statsReport)->first()
				: null;

			$validatedKey = $statsReport && $statsReport->validated
				? 'validated'
				: 'not-validated';

			$centerData[$center->localRegion][$validatedKey][$center->name] = array(
				'name'        => $center->name,
				'localRegion' => $center->localRegion,
				'validated'   => $statsReport ? $statsReport->validated : false,
				'rating'      => $actualData ? $actualData->rating : '-',
				'updatedAt'   => $statsReport ? date('M d, Y @ g:ia T', strtotime($statsReport->updatedAt . ' UTC')) : '-',
				'updatedBy'   => $user ? $user->firstName : '-',
			);
		}

		$results['eastCount'] = count($centerData['East']['validated']) + count($centerData['East']['not-validated']);
		$results['westCount'] = count($centerData['West']['validated']) + count($centerData['West']['not-validated']);
		$results['eastComplete'] = count($centerData['East']['validated']);
		$results['westComplete'] = count($centerData['West']['validated']);

		$centerData = array_merge($centerData['East']['validated'],
								  $centerData['East']['not-validated'],
								  $centerData['West']['validated'],
								  $centerData['West']['not-validated']);

		return view('home')->with(['reportingDate' => $reportingDate,
								   'centersData' => $centerData,
								   'reportingDates' => $reportingDates,
								   'timezone' => $timezone,
								   'results' => $results]);
	}

	public function setTimezone()
	{
		if (Request::has('timezone')) {
			Session::put('timezone', Request::get('timezone'));
		}
	}

	// TODO: Duplicated from Import. put somewhere else
	protected function getExpectedReportDate()
	{
		$expectedDate = null;
		if (Carbon::now()->dayOfWeek == Carbon::FRIDAY) {
			$expectedDate = Carbon::now();
		} else {
			$expectedDate = new Carbon('last friday');
		}
		return $expectedDate->startOfDay();
	}
}
