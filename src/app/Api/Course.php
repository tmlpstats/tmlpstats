<?php namespace TmlpStats\Api;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;

/**
 * Courses
 */
class Course extends ApiBase
{
    protected $validProperties = [
        'center' => [
            'owner' => 'course',
            'type'  => 'Center',
        ],
        'startDate' => [
            'owner' => 'course',
            'type'  => 'date',
        ],
        'type' => [
            'owner' => 'course',
            'type'  => 'string',
        ],
        'location' => [
            'owner' => 'course',
            'type'  => 'string',
        ],
        'quarterStartTer' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'quarterStartStandardStarts' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'quarterStartXfer' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'currentTer' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'currentStandardStarts' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'currentXfer' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'completedStandardStarts' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'potentials' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'registrations' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'guestsPromised' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'guestsInvited' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'guestsConfirmed' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
        'guestsAttended' => [
            'owner' => 'courseData',
            'type'  => 'int',
        ],
    ];

    public function create(array $data)
    {
        $data = $this->parseInputs($data, ['center', 'startDate', 'type']);
        $data['type'] = strtoupper($data['type']);

        if (!in_array($data['type'], ['CAP', 'CPC'])) {
            throw new ApiExceptions\BadRequestException('Invalid type provided');
        }

        $courseData = [
            'center_id'  => $data['center']->id,
            'start_date' => $data['startDate'],
            'type'       => $data['type'],
        ];

        // London has a special situation where international (INTL) and local stats
        // are reported separately for courses. This means they may have 2 "courses" for a single center/date
        if ($data['center']->name === 'London' && isset($data['location'])) {
            $courseData['is_international'] = (strtoupper($data['location']) === 'INTL');
        }

        $course = Models\Course::firstOrNew($courseData);

        // Create only creates
        if ($course->exists) {
            throw new ApiExceptions\BadRequestException('Course already exists');
        }

        if (isset($data['location'])) {
            $course->location = $data['location'];
        }

        $course->save();

        // Hacky. Make sure we have a data object for the new course so we can get it's data later
        $this->getWeekData($course);

        return $course->load('center');
    }

    public function update(Models\Course $course, array $data)
    {
        $data = $this->parseInputs($data);

        foreach ($data as $property => $value) {
            if ($this->validProperties[$property]['owner'] === 'course') {
                if ($property === 'center') {
                    $property = 'centerId';
                    $value = $value->id;
                } else if ($property === 'type') {
                    $value = strtoupper($value);
                    // Would be nice if we just had an enum type, but it's not east to accomplish that with the
                    // current Parsers implementation
                    if (!in_array($value, ['CAP', 'CPC'])) {
                        throw new ApiExceptions\BadRequestException('Unrecognized type');
                    }
                }

                if ($course->$property !== $value) {
                    $course->$property = $value;
                }
            }
        }

        if ($course->isDirty()) {
            $course->save();
        }

        return $course->load('center');
    }

    public function allForCenter(Models\Center $center, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($center);
        } else if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            $dateStr = $reportingDate->toDateString();
            throw new ApiExceptions\BadRequestException("Reporting date must be a Friday.");
        }

        $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);

        $reports = Models\StatsReport::byCenter($center)
                                     ->byQuarter($quarter)
                                     ->official()
                                     ->orderBy('reporting_date', 'asc')
                                     ->with('courseData')
                                     ->get();

        $allCourses = [];
        foreach ($reports as $report) {
            if ($report->reportingDate->gt($reportingDate)) {
                continue;
            }

            foreach ($report->courseData as $courseData) {
                // Store indexed here so we end up with only the most recent one for each course
                $allCourses[$courseData->courseId] = $this->getWeekData($courseData->course, $report->reportingDate);
            }
        }

        // Pick up any courses that are new this week
        $thisReport = LocalReport::getStatsReport($center, $reportingDate, true);
        foreach ($thisReport->courseData() as $courseData) {
            if (isset($allCourses[$courseData->courseId])) {
                continue;
            }

            $allCourses[$courseData->courseId] = $this->getWeekData($courseData->course, $report->reportingDate);
        }

        usort($allCourses, function ($a, $b) {
            if ($a->course->startDate->eq($b->course->startDate)) {
                return 0;
            }
            return $a->course->startDate->lt($b->course->startDate) ? -1 : 1;
        });

        return array_values($allCourses);
    }

    public function getWeekData(Models\Course $course, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($course->center);
        } else if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            $dateStr = $reportingDate->toDateString();
            throw new ApiExceptions\BadRequestException("Reporting date must be a Friday.");
        }

        $getUnsubmitted = $reportingDate->gte(Carbon::now($course->center->timezone)->startOfDay());

        $report = LocalReport::getStatsReport($course->center, $reportingDate, $getUnsubmitted);

        $response = Models\CourseData::firstOrNew([
            'course_id' => $course->id,
            'stats_report_id' => $report->id,
        ]);

        // If we're creating a new data object now, pre-populate it with data from last week
        if (!$response->exists) {

            $lastWeeksReport = Models\StatsReport::byCenter($course->center)
                                                 ->reportingDate($reportingDate->copy()->subWeek())
                                                 ->official()
                                                 ->first();

            // It's the center's first official report or they didn't submit last week
            $lastWeeksData = null;
            if ($lastWeeksReport) {
                $lastWeeksData = Models\CourseData::byStatsReport($lastWeeksReport)
                                                  ->ByCourse($course)
                                                  ->first();
            }

            if ($lastWeeksData) {
                $response->mirror($lastWeeksData);
            }

            $response->save();
        }

        return $response->load('course', 'course.center', 'statsReport');
    }

    public function setWeekData(Models\Course $course, Carbon $reportingDate, array $data)
    {
        $data = $this->parseInputs($data);

        $courseData = $this->getWeekData($course, $reportingDate);

        foreach ($data as $property => $value) {
            if ($this->validProperties[$property]['owner'] === 'courseData' && $courseData->$property !== $value) {
                $courseData->$property = $value;
            }
        }

        if ($courseData->isDirty()) {
            $courseData->save();
        }

        return $courseData->load('course', 'course.center', 'statsReport');
    }
}

