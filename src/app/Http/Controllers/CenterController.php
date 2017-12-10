<?php

namespace TmlpStats\Http\Controllers;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;

class CenterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->context = App::make(Api\Context::class);
    }
    public function dashboard($abbr)
    {
        $center = Models\Center::abbreviation($abbr)->firstOrFail();

        $this->context->setCenter($center);
        $this->setCenter($center);

        $statsReport = Models\StatsReport::byCenter($center)
            ->official()
            ->orderBy('reporting_date', 'desc')
            ->firstOrFail();

        // Short circuit to most recent local report until we make a more useful dashboard
        return redirect($statsReport->getUriLocalReport());

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
        $center = Models\Center::abbreviation($abbr)->firstOrFail();
        $reportingDate = Carbon::parse($reportingDate, 'UTC');

        $this->context->setDateSelectAction('CenterController@submission', ['abbr' => $abbr]);
        $this->context->setReportingDate($reportingDate);
        $this->context->setRegion($center->region, false);

        $alreadySubmitted = false;
        if (Models\StatsReport::byCenter($center)->reportingDate($reportingDate)->official()->count()) {
            $alreadySubmitted = true;
        }

        return view('centers.submission', compact('center', 'reportingDate', 'alreadySubmitted'));
    }

    public function nextQtrAccountabilities($abbr, $reportingDate = null)
    {
        $center = Models\Center::abbreviation($abbr)->firstorFail();
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
