<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Center;
use TmlpStats\User;
use TmlpStats\StatsReport;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

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
		$centerData = array();
		$reportingDate = $this->getExpectedReportDate();
		$centers = Center::active()->orderBy('local_region', 'asc')->get();

		foreach ($centers as $center) {

			$statsReport = $center->statsReports()->reportingDate($reportingDate)->first();

			$user = $statsReport
				? User::find($statsReport->user_id)
				: null;

			$actualData = $statsReport
				? CenterStatsData::actual()->reportingDate($reportingDate)->statsReport($statsReport)->first()
				: null;

			$centerData[$center->name] = array(
				'name'        => $center->name,
				'localRegion' => $center->localRegion,
				'validated'   => $statsReport ? $statsReport->validated : false,
				'rating'      => $actualData ? $actualData->rating : '-',
				'updatedAt'   => $statsReport ? $statsReport->updatedAt : '-',
				'updatedBy'   => $user ? $user->firstName : '-',
			);
		}
		return view('home')->with(['reportingDate' => $reportingDate, 'centersData' => $centerData]);
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
		return $expectedDate->startOfDay()->toDateString();
	}
}
