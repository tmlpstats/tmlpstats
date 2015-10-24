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
        $teamMembers = Cache::tags(["statsReport{$id}"])->get($cacheKey);

        if (!$teamMembers) {
            $statsReport = StatsReport::find($id);

            if (!$statsReport) {
                return null;
            }

            $teamMembers = [
                'team1' => [],
                'team2' => [],
            ];

            $tdo = [
                'team1' => 0,
                'team2' => 0,
                'total' => 0,
            ];

            $gitw = [
                'team1' => 0,
                'team2' => 0,
                'total' => 0,
            ];

            $withdraws = [
                'team1' => 0,
                'team2' => 0,
                'total' => 0,
                'ctw'   => 0,
                'codes' => [],
            ];

            // Team Members
            $memberData = TeamMemberData::byStatsReport($statsReport)->with('teamMember')->get();
            foreach ($memberData as $data) {
                if ($data->teamMember->teamYear == 1) {
                    $teamMembers['team1'][] = $data;

                    if ($data->tdo) {
                        $tdo['team1']++;
                        $tdo['total']++;
                    }

                    if ($data->gitw) {
                        $gitw['team1']++;
                        $gitw['total']++;
                    }

                    if ($data->withdrawCode) {
                        $withdraws['team1']++;
                        $withdraws['total']++;
                        if (isset($withdraws['codes'][$data->withdrawCode->display])) {
                            $withdraws['codes'][$data->withdrawCode->display]++;
                        } else {
                            $withdraws['codes'][$data->withdrawCode->display] = 1;
                        }
                    } else if ($data->ctw) {
                        $withdraws['ctw']++;
                    }
                } else {
                    $teamMembers['team2'][] = $data;

                    if ($data->tdo) {
                        $tdo['team2']++;
                        $tdo['total']++;
                    }

                    if ($data->gitw) {
                        $gitw['team2']++;
                        $gitw['total']++;
                    }

                    if ($data->withdrawCode) {
                        $withdraws['team2']++;
                        $withdraws['total']++;
                        if (isset($withdraws['codes'][$data->withdrawCode->display])) {
                            $withdraws['codes'][$data->withdrawCode->display]++;
                        } else {
                            $withdraws['codes'][$data->withdrawCode->display] = 1;
                        }
                    } else if ($data->ctw) {
                        $withdraws['ctw']++;
                    }
                }
            }

            $t1Total = count($teamMembers['team1']) - $withdraws['team1'];
            $t2Total = count($teamMembers['team2']) - $withdraws['team2'];

            if ($t1Total) {
                $tdo['team1'] = round(($tdo['team1'] / ($t1Total)) * 100);
                $gitw['team1'] = round(($gitw['team1'] / ($t1Total)) * 100);
            }
            if ($t2Total) {
                $tdo['team2'] = round(($tdo['team2'] / ($t2Total)) * 100);
                $gitw['team2'] = round(($gitw['team2'] / ($t2Total)) * 100);
            }
            if ($t1Total + $t2Total) {
                $tdo['total'] = round(($tdo['total'] / ($t1Total + $t2Total)) * 100);
                $gitw['total'] = round(($gitw['total'] / ($t1Total + $t2Total)) * 100);
            }

            $teamMembers['tdo'] = $tdo;
            $teamMembers['gitw'] = $gitw;
            $teamMembers['withdraws'] = $withdraws;
        }
        Cache::tags(["statsReport{$id}"])->put($cacheKey, $teamMembers, static::STATS_REPORT_CACHE_TTL);

        return $teamMembers;
    }
}
