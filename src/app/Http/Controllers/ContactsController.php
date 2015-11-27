<?php

namespace TmlpStats\Http\Controllers;

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

    public function getByStatsReport(StatsReport $statsReport)
    {
        // Contacts
        $contacts = array();
        $accountabilities = array(
            'programManager',
            'classroomLeader',
            't1tl',
            't2tl',
            'statistician',
            'statisticianApprentice',
        );
        foreach ($accountabilities as $accountability) {
            $accountabilityObj = Accountability::name($accountability)->first();
            $contacts[$accountabilityObj->display] = $statsReport->center->getAccountable($accountability);
        }

        return $contacts;
    }
}
