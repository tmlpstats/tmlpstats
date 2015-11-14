<?php

namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use TmlpStats\GlobalReport;
use TmlpStats\Http\Requests;
use TmlpStats\Region;
use TmlpStats\StatsReport;
use TmlpStats\TmlpRegistrationData;

class TmlpRegistrationsController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
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
     * @param  int $id
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getByGlobalReport(GlobalReport $globalReport, Region $region)
    {
        $statsReports = $globalReport->statsReports()
            ->byRegion($region)
            ->reportingDate($globalReport->reportingDate)
            ->get();

        $registrations = [];
        foreach ($statsReports as $report) {

            $reportRegistrations = $this->getByStatsReport($report);
            foreach ($reportRegistrations as $registration) {
                $registrations[] = $registration;
            }
        }

        return $registrations;
    }

    public function getByStatsReport(StatsReport $statsReport)
    {
        return TmlpRegistrationData::byStatsReport($statsReport)->with('registration.person', 'committedTeamMember.person')->get();
    }
}
