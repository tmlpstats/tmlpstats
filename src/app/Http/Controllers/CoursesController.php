<?php

namespace TmlpStats\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use TmlpStats\CourseData;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\StatsReport;

class CoursesController extends Controller
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
        $cacheKey = "statsReport{$id}:courses";
        $courses = Cache::get($cacheKey);

        if (!$courses) {
            $statsReport = StatsReport::find($id);

            $this->statsReport = $statsReport;

            if (!$statsReport) {
                return null;
            }


            $courses = [
                'CAP'               => [],
                'CPC'               => [],
                'completedThisWeek' => [],
            ];

            $thisWeek = $statsReport->reportingDate;
            $lastWeek = clone $thisWeek;
            $lastWeek->subWeek();
            $completedCourses = [];

            // Courses
            $courseData = CourseData::byStatsReport($statsReport)->with('course')->get();
            foreach ($courseData as $data) {
                if ($data->course->type == 'CAP') {
                    $courses['CAP'][] = $data;
                    if ($data->course->startDate->gt($lastWeek) && $data->course->startDate->lt($thisWeek)) {
                        $completedCourses[] = $data;
                    }
                } else {
                    $courses['CPC'][] = $data;
                    if ($data->course->startDate->gt($lastWeek) && $data->course->startDate->lt($thisWeek)) {
                        $completedCourses[] = $data;
                    }
                }
            }

            $courses['completedThisWeek'] = $completedCourses;
        }
        Cache::put($cacheKey, $courses, static::CACHE_TTL);



        return $courses;
    }
}
