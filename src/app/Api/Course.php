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

    public function getWeekData(Models\Course $course, Carbon $reportingDate)
    {
        $cached = $this->checkCache(compact('course', 'reportingDate'));
        if ($cached) {
            return $cached;
        }

        $report = LocalReport::getStatsReport($course->center, $reportingDate);

        $response = Models\CourseData::firstOrCreate([
            'course_id' => $course->id,
            'stats_report_id' => $report->id,
        ])->load('course', 'course.center', 'statsReport');

        $this->putCache($response);

        return $response;
    }

    public function setWeekData(Models\Course $course, Carbon $reportingDate, array $data)
    {
        $data = $this->parseInputs($data);

        $report = LocalReport::getStatsReport($course->center, $reportingDate);

        $courseData = Models\CourseData::firstOrCreate([
            'course_id' => $course->id,
            'stats_report_id' => $report->id,
        ]);

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

