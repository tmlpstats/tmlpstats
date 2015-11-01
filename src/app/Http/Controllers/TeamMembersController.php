<?php

namespace TmlpStats\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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

            $teamMembers = TeamMemberData::byStatsReport($statsReport)->with('teamMember')->get();
        }
        Cache::tags(["statsReport{$id}"])->put($cacheKey, $teamMembers, static::STATS_REPORT_CACHE_TTL);

        return $teamMembers;
    }
}
