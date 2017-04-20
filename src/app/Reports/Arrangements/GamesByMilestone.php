<?php
namespace TmlpStats\Reports\Arrangements;

use Carbon\Carbon;
use TmlpStats\Domain;

class GamesByMilestone extends BaseArrangement
{
    /*
     * Builds an array of weekly promise/actual pairs
     * broken down by milestone
     *
     * Requires input weeks to be formatted like the output from
     * GamesByWeek arrangement
     */
    public function build($data)
    {
        $weeks = $data['weeks'];
        $quarter = $data['quarter'];
        $center = isset($data['center']) ? $data['center'] : null;

        if ($center) {
            $centerQuarter = Domain\CenterQuarter::ensure($center, $quarter);
            $classroom1Date = $centerQuarter->classroom1Date;
            $classroom2Date = $centerQuarter->classroom2Date;
            $classroom3Date = $centerQuarter->classroom3Date;
        } else {
            $classroom1Date = $quarter->getClassroom1Date();
            $classroom2Date = $quarter->getClassroom2Date();
            $classroom3Date = $quarter->getClassroom3Date();
        }

        $reportData = [];
        foreach ($weeks as $dateString => $weekData) {
            $weekDate = Carbon::createFromFormat('Y-m-d', $dateString)->startOfDay();
            if ($weekDate->lte($classroom1Date)) {
                $classroom = 0;
            } else if ($weekDate->lte($classroom2Date)) {
                $classroom = 1;
            } else if ($weekDate->lte($classroom3Date)) {
                $classroom = 2;
            } else {
                $classroom = 3;
            }

            $reportData[$classroom][$dateString] = $weekData;
        }

        return compact('reportData');
    }
}
