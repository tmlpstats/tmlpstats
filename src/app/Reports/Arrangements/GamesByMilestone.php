<?php namespace TmlpStats\Reports\Arrangements;

use Carbon\Carbon;

class GamesByMilestone extends BaseArrangement
{

    /* Builds an array of weekly promise/actual pairs
     * broken down by milestone
     *
     */
    public function build($data)
    {
        $weeks = $data['weeks'];
        $quarter = $data['quarter'];

        $reportData = [];
        foreach ($weeks as $dateString => $weekData) {
            $weekDate = Carbon::createFromFormat('Y-m-d', $dateString)->startOfDay();
            if ($weekDate->lte($quarter->classroom1Date)) {
                $classroom = 0;
            } else if ($weekDate->lte($quarter->classroom2Date)) {
                $classroom = 1;
            } else if ($weekDate->lte($quarter->classroom3Date)) {
                $classroom = 2;
            } else {
                $classroom = 3;
            }

            $reportData[$classroom][$dateString] = $weekData;
        }

        return compact('reportData');
    }
}
