<?php

namespace TmlpStats\Http\Controllers;

use App;
use Illuminate\Http\Request;
use TmlpStats\Center;
use TmlpStats\Http\Requests;
use TmlpStats\StatsReport;

class CenterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $center = Center::findOrFail($id);

        $this->authorize($center);

        $roster = $center->getTeamRoster();

        $latestReport = StatsReport::byCenter($center)
                                   ->orderBy('reporting_date', 'desc')
                                   ->first();

        $statsReportsThisWeek = StatsReport::byCenter($center)
                                           ->reportingDate($latestReport->reportingDate)
                                           ->get();
        $reportsThisWeek      = [];
        foreach ($statsReportsThisWeek as $report) {
            $reportsThisWeek[$report->id] = $report->reportingDate->format('M d, Y');
        }

        return view('centers.show')->with(compact('roster', 'statsReportsThisWeek'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function dashboard($abbr)
    {
        $center = Center::abbreviation($abbr)->first();
        if (!$center) {
            abort(404);
        }

        $this->setCenter($center);

        $statsReport = StatsReport::byCenter($center)
                                  ->official()
                                  ->orderBy('reporting_date', 'desc')
                                  ->first();

        $weekData = [];
        $reportUrl = '';
        try {
            $weekData = $statsReport
                ? App::make(StatsReportController::class)->getSummaryPageData($statsReport)
                : [];

            $reportUrl = $statsReport
                ? StatsReportController::getUrl($statsReport)
                : '';
        } catch (\Exception $e) {
            // An exception may be thrown if a stats report is from a previous quarter and there is incomplete promise data.
            $statsReport = null;
        }

        if ($weekData === null) {
            $weekData = [];
        }

        $liveScoreboard = true;
        $editableLiveScoreboard = false;

        $data = compact(
            'center',
            'statsReport',
            'reportUrl',
            'liveScoreboard',
            'editableLiveScoreboard'
        );

        return view('centers.dashboard')->with(array_merge($data, $weekData));
    }
}
