<?php namespace TmlpStats\Reports\Arrangements;

class CoursesByCenter extends BaseArrangement
{
    public function build($data)
    {
        $coursesData = $data['coursesData'];

        $reportData = [];

        foreach ($coursesData as $data) {
            $reportData[$data->center->name][] = $data;
        }

        return compact('reportData');
    }
}
