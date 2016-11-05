<?php

namespace TmlpStats\Http\Controllers;

use App;
use Carbon\Carbon;
use TmlpStats\Api;
use TmlpStats\Center;
use TmlpStats\StatsReport;

class CenterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->context = App::make(Api\Context::class);
    }
    public function dashboard($abbr)
    {
        $center = Center::abbreviation($abbr)->first();
        if (!$center) {
            abort(404);
        }
        $context = App::make(Api\Context::class);
        $context->setCenter($center);
        $this->setCenter($center);

        $statsReport = StatsReport::byCenter($center)
            ->official()
            ->orderBy('reporting_date', 'desc')
            ->first();

        $weekData = [];
        $reportUrl = '';
        if ($statsReport) {
            try {
                $weekData = App::make(StatsReportController::class)->getSummaryPageData($statsReport);
                $reportUrl = StatsReportController::getUrl($statsReport);
            } catch (\Exception $e) {
                // An exception may be thrown if a stats report is from a previous quarter and there is incomplete promise data.
                $statsReport = null;
            }
        }

        $weekData = $weekData ?: [];

        $liveScoreboard = true;

        $data = compact(
            'center',
            'statsReport',
            'reportUrl',
            'liveScoreboard'
        );

        return view('centers.dashboard')->with(array_merge($data, $weekData));
    }

    public function submission($abbr, $reportingDate)
    {
        $center = Center::abbreviation($abbr)->firstorFail();
        $this->context->setDateSelectAction('CenterController@submission', ['abbr' => $abbr]);
        $this->context->setReportingDate(Carbon::parse($reportingDate, 'UTC'));
        $this->context->setRegion($center->region);

        return view('centers.submission', compact('center'));
    }

    public function nextQtrAccountabilities($abbr, $reportingDate = null)
    {
        $center = Center::abbreviation($abbr)->firstorFail();
        $this->authorize('submitStats', $center);
        $this->context->setCenter($center);
        if ($reportingDate === null) {
            $reportingDate = $this->context->getReportingDate();
        } else {
            $reportingDate = Carbon::parse($reportingDate, 'UTC');
            $this->context->setReportingDate($reportingDate);
        }

        return view('centers.next_qtr_accountabilities', compact('center', 'reportingDate'));
    }

}
