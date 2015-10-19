<?php

namespace TmlpStats\Http\Controllers;

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
        $statsReport = StatsReport::find($id);

        $this->statsReport = $statsReport;

        if (!$statsReport) {
            return null;
        }

        $nextQuarter = $statsReport->quarter->getNextQuarter();

        // Tmlp Registrations
        $tmlpRegistrations = array();
        $registrationsData = TmlpRegistrationData::byStatsReport($statsReport)->with('registration')->get();
        foreach ($registrationsData as $data) {
            if ($data->incomingQuarterId !== $nextQuarter->id) {
                $tmlpRegistrations['future'][] = $data;
            } else if ($data->registration->teamYear == 1) {
                $tmlpRegistrations['team1'][] = $data;
            } else {
                $tmlpRegistrations['team2'][] = $data;
            }
        }

        return $tmlpRegistrations;
    }
}
