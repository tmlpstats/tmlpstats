<?php
namespace TmlpStats\Http\Controllers;

use App;
use Cache;
use Illuminate\Http\Request;
use TmlpStats\GlobalReport;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\Region;
use TmlpStats\StatsReport;
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

    public function getByGlobalReport($id, Region $region)
    {
        $cacheKey = $region === null
            ? "globalreport{$id}:teammembers"
            : "globalreport{$id}:region{$region->id}:teammembers";
        $teamMembers = ($this->useCache()) ? Cache::tags(["globalReport{$id}"])->get($cacheKey) : false;

        if (!$teamMembers) {
            $globalReport = GlobalReport::find($id);

            $statsReports = $globalReport->statsReports()
                ->byRegion($region)
                ->reportingDate($globalReport->reportingDate)
                ->get();

            $teamMembers = [];
            foreach ($statsReports as $report) {

                $reportTeamMembers = $this->getByStatsReport($report->id);
                foreach ($reportTeamMembers as $member) {
                    $teamMembers[] = $member;
                }
            }
        }
        Cache::tags(["globalReport{$id}"])->put($cacheKey, $teamMembers, static::CACHE_TTL);

        return $teamMembers;
    }

    public function getByStatsReport($id)
    {
        $cacheKey = "statsReport{$id}:teammembers";
        $teamMembers = ($this->useCache()) ? Cache::tags(["statsReport{$id}"])->get($cacheKey) : false;

        if (!$teamMembers) {
            $statsReport = StatsReport::find($id);

            if (!$statsReport) {
                return null;
            }

            $teamMembers = TeamMemberData::byStatsReport($statsReport)->with('teamMember.person')->get();
        }
        Cache::tags(["statsReport{$id}"])->put($cacheKey, $teamMembers, static::STATS_REPORT_CACHE_TTL);

        return $teamMembers;
    }
}
