<?php

namespace TmlpStats\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
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

    public function getByStatsReport($id)
    {
        $cacheKey = "statsReport{$id}:tmlpregistrations";
        $tmlpRegistrations = (static::USE_CACHE) ? Cache::tags(["statsReport{$id}"])->get($cacheKey) : false;

        if (!$tmlpRegistrations) {
            $statsReport = StatsReport::find($id);

            if (!$statsReport) {
                return null;
            }

            $tmlpRegistrations = [
                'team1' => [],
                'team2' => [],
                'future' => [],
            ];

            $applications = [
                'notSent'  => 0,
                'out'      => 0,
                'waiting'  => 0,
                'approved' => 0,
                'wd'       => 0,
                'total'    => 0,
            ];

            $withdraws = [
                'team1' => 0,
                'team2' => 0,
                'total' => 0,
                'codes' => [],
            ];

            $nextQuarter = $statsReport->quarter->getNextQuarter();

            // Tmlp Registrations
            $registrationsData = TmlpRegistrationData::byStatsReport($statsReport)->with('registration')->get();
            foreach ($registrationsData as $data) {
                if ($data->incomingQuarterId !== $nextQuarter->id) {
                    $tmlpRegistrations['future'][] = $data;

                    $applications['total']++;
                    if ($data->withdrawCodeId) {
                        $applications['wd']++;
                    } else if ($data->apprDate) {
                        $applications['approved']++;
                    } else if ($data->appInDate) {
                        $applications['waiting']++;
                    } else if ($data->appOutDate) {
                        $applications['out']++;
                    } else {
                        $applications['notSent']++;
                    }

                    if ($data->withdrawCode) {
                        if ($data->registration->teamYear == 1) {
                            $withdraws['team1']++;
                        } else {
                            $withdraws['team2']++;
                        }
                        $withdraws['total']++;
                        if (isset($withdraws['codes'][$data->withdrawCode->display])) {
                            $withdraws['codes'][$data->withdrawCode->display]++;
                        } else {
                            $withdraws['codes'][$data->withdrawCode->display] = 1;
                        }
                    }
                } else if ($data->registration->teamYear == 1) {
                    $tmlpRegistrations['team1'][] = $data;

                    $applications['total']++;
                    if ($data->withdrawCodeId) {
                        $applications['wd']++;
                    } else if ($data->apprDate) {
                        $applications['approved']++;
                    } else if ($data->appInDate) {
                        $applications['waiting']++;
                    } else if ($data->appOutDate) {
                        $applications['out']++;
                    } else {
                        $applications['notSent']++;
                    }

                    if ($data->withdrawCode) {
                        $withdraws['team1']++;
                        $withdraws['total']++;
                        if (isset($withdraws['codes'][$data->withdrawCode->display])) {
                            $withdraws['codes'][$data->withdrawCode->display]++;
                        } else {
                            $withdraws['codes'][$data->withdrawCode->display] = 1;
                        }
                    }
                } else {
                    $tmlpRegistrations['team2'][] = $data;

                    $applications['total']++;
                    if ($data->withdrawCodeId) {
                        $applications['wd']++;
                    } else if ($data->apprDate) {
                        $applications['approved']++;
                    } else if ($data->appInDate) {
                        $applications['waiting']++;
                    } else if ($data->appOutDate) {
                        $applications['out']++;
                    } else {
                        $applications['notSent']++;
                    }

                    if ($data->withdrawCode) {
                        $withdraws['team2']++;
                        $withdraws['total']++;
                        if (isset($withdraws['codes'][$data->withdrawCode->display])) {
                            $withdraws['codes'][$data->withdrawCode->display]++;
                        } else {
                            $withdraws['codes'][$data->withdrawCode->display] = 1;
                        }
                    }
                }
            }
            $tmlpRegistrations['applications'] = $applications;
            $tmlpRegistrations['withdraws'] = $withdraws;
        }
        Cache::tags(["statsReport{$id}"])->put($cacheKey, $tmlpRegistrations, static::STATS_REPORT_CACHE_TTL);

        return $tmlpRegistrations;
    }
}
