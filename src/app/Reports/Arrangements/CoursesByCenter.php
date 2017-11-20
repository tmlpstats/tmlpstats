<?php
namespace TmlpStats\Reports\Arrangements;

class CoursesByCenter extends BaseArrangement
{
    public function build($data)
    {
        $reportData = [];

        foreach ($data['coursesData'] as $course) {
            $reportData[$course->center->name][] = $course;
        }

        return compact('reportData');
    }
}
