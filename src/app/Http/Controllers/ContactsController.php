<?php

namespace TmlpStats\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use TmlpStats\Accountability;
use TmlpStats\Http\Requests;
use TmlpStats\StatsReport;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->middleware('auth');
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


    public function getByStatsReport($id)
    {
        $cacheKey = "statsReport{$id}:contacts";
        $contacts = ($this->useCache()) ? Cache::tags(["statsReport{$id}"])->get($cacheKey) : false;

        if (!$contacts) {
            $statsReport = StatsReport::find($id);

            if (!$statsReport) {
                return null;
            }

            // Contacts
            $contacts = array();
            $accountabilities = array(
                'programManager',
                'classroomLeader',
                'team1TeamLeader',
                'team2TeamLeader',
                'teamStatistician',
                'teamStatisticianApprentice',
            );
            foreach ($accountabilities as $accountability) {
                $accountabilityObj = Accountability::name($accountability)->first();
                $contacts[$accountabilityObj->display] = $statsReport->center->getAccountable($accountability);
            }
        }
        Cache::tags(["statsReport{$id}"])->put($cacheKey, $contacts, static::STATS_REPORT_CACHE_TTL);

        return $contacts;
    }
}
