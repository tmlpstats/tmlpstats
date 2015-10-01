<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\Import\Xlsx\XlsxArchiver;

use TmlpStats\Import\ImportManager;
use TmlpStats\Center;
use TmlpStats\User;
use TmlpStats\StatsReport;
use TmlpStats\CenterStatsData;
use TmlpStats\Quarter;

use Carbon\Carbon;

use Auth;
use Request;
use Input;

class AdminController extends Controller {

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
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

            $localRegion = $center->localRegion ?: 0;

            $statsReport = $center->statsReports()
                                  ->reportingDate($reportingDate->toDateString())
                                  ->orderBy('submitted_at', 'desc')
                                  ->first();

            $user = $statsReport
                ? User::find($statsReport->user_id)
                : null;

            $sheetUrl = $statsReport && XlsxArchiver::getInstance()->getSheetPath($statsReport)
                ? url("/statsreports/{$statsReport->id}/download")
                : null;

            $updatedAt = $statsReport
                ? Carbon::createFromFormat('Y-m-d H:i:s', $statsReport->updatedAt, 'UTC')
                : null;

            if ($updatedAt) {
                $updatedAt->setTimezone($center->timezone);
            }

            $centerResults = array(
                'name'          => $center->name,
                'statsReportId' => $statsReport ? $statsReport->id : 0,
                'localRegion'   => $center->localRegion,
                'complete'      => $statsReport ? $statsReport->validated : false,
                'locked'        => $statsReport ? $statsReport->locked : false,
                'rating'        => $statsReport ? $statsReport->getRating() : '-',
                'updatedAt'     => $updatedAt ? $updatedAt->format('M d, Y @ g:ia T') : '-',
                'updatedBy'     => $user ? $user->firstName : '-',
                'sheet'         => $statsReport ? $sheetUrl : null,
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

        return view('admin.dashboard')->with(['reportingDate' => $reportingDate,
                                              'reportingDates' => $reportingDates,
                                              'selectedRegion' => $region,
                                              'regionsData' => $regionsData]);
    }

    protected static function sortByComplete($a, $b)
    {
        if ($a['complete'] != $b['complete']) {
            return $a['complete'] ? -1 : 1; // reverse order to get sort in DESC order
        } else {
            return strcmp($a['name'], $b['name']);
        }
    }
}
