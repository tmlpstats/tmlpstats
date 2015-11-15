<?php
namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use TmlpStats\GlobalReport;
use TmlpStats\Http\Requests;
use TmlpStats\Region;
use TmlpStats\StatsReport;
use TmlpStats\TeamMember;
use TmlpStats\TeamMemberData;

class TeamMembersController extends Controller
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

        $teamMembers = [];
        foreach ($statsReports as $report) {

            $reportTeamMembers = $this->getByStatsReport($report);
            foreach ($reportTeamMembers as $member) {
                $teamMembers[] = $member;
            }
        }

        return $teamMembers;
    }

    public function getByStatsReport(StatsReport $statsReport)
    {
        return TeamMemberData::byStatsReport($statsReport)->with('teamMember.person')->get();
    }
}
