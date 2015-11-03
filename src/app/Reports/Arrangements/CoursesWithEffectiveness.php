<?php namespace TmlpStats\Reports\Arrangements;

class CoursesWithEffectiveness extends BaseArrangement
{

    /* Builds an array of courses with effectiveness calculations
     *
     */
    public function build($data)
    {
        $courses = $data['courses'];
        $reportingDate = $data['reportingDate'];

        $reportData = [];
        foreach ($courses as $courseData) {

            $type = $courseData->course->type;

            $course = [
                'location'  => $courseData->course->location ?: $courseData->course->center->name,
                'startDate' => $courseData->course->startDate,
                'type'      => $type,
            ];

            $copyFields = [
                'quarterStartTer',
                'quarterStartStandardStarts',
                'quarterStartXfer',
                'currentTer',
                'currentStandardStarts',
                'currentXfer',
                'completedStandardStarts',
                'potentials',
                'registrations',
            ];

            foreach ($copyFields as $field) {
                $course[$field] = $courseData->$field;
            }

            if ($course['startDate']->lt($reportingDate)) {
                $course['completionStats']['registrationFulfillment'] = $courseData->currentTer
                    ? round(($courseData->currentStandardStarts / $courseData->currentTer) * 100)
                    : 0;
                $course['completionStats']['registrationEffectiveness'] = $courseData->potentials
                    ? round(($courseData->registrations / $courseData->potentials) * 100)
                    : 0;

                $type = 'completed';
            }

            $reportData[$type][] = $course;
        }

        return compact('reportData');
    }
}
