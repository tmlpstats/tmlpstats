<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;

/**
 * Courses
 */
class Course extends ApiBase
{
    public function create(array $data)
    {
        $input = Domain\Course::fromArray($data, ['center', 'startDate', 'type']);

        $courseData = [
            'center_id' => $input->center->id,
            'start_date' => $input->startDate,
            'type' => $input->type,
        ];

        // London has a special situation where international (INTL) and local stats
        // are reported separately for courses. This means they may have 2 "courses"
        // for a single center/date
        // We only need to worry about this when creating a new course
        if ($input->has('location') && $input->center->name === 'London') {
            $courseData['is_international'] = (strtoupper($input->location) === 'INTL');
        }

        $course = Models\Course::firstOrNew($courseData);

        // Create only creates
        if ($course->exists) {
            throw new ApiExceptions\BadRequestException('Course already exists');
        }

        if ($input->has('location')) {
            $course->location = $input->location;
        }

        $course->save();

        // Make sure we have a data object for the new course so we can get it's data later
        $this->getWeekData($course);

        return $course->load('center');
    }

    public function update(Models\Course $course, array $data)
    {
        $courseDomain = Domain\Course::fromArray($data);
        $courseDomain->fillModel(null, $course);

        $course->save();

        return $course->load('center');
    }

    public function allForCenter(Models\Center $center, $includeInProgress = false, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($center);
        } else {
            App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);
        }

        $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);

        $reports = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->official()
            ->where('reporting_date', '<=', $reportingDate)
            ->orderBy('reporting_date', 'asc')
            ->with('courseData')
            ->get();

        $allCourses = [];

        foreach ($reports as $report) {
            foreach ($report->courseData as $courseData) {
                // Store indexed here so we end up with only the most recent one for each course
                $allCourses[$courseData->courseId] = Domain\Course::fromModel($courseData);
            }
        }

        // Pick up any courses that are new this week
        $thisReport = LocalReport::getStatsReport($center, $reportingDate, true);
        foreach ($thisReport->courseData() as $courseData) {
            if (isset($allCourses[$courseData->courseId])) {
                continue;
            }

            $allCourses[$courseData->courseId] = Domain\Course::fromModel($courseData);
        }

        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\Course::class);
            foreach ($found as $courseData) {
                $courseData->meta['localChanges'] = true;
                $allCourses[$courseData->id] = $courseData;
            }
        }

        usort($allCourses, function ($a, $b) {
            if ($a->startDate->eq($b->startDate)) {
                return 0;
            }

            return $a->startDate->lt($b->startDate) ? -1 : 1;
        });

        foreach ($allCourses as $course) {
            $course->meta = $this->getCourseMeta($course, $center, $reportingDate);
        }

        return array_values($allCourses);
    }

    public function getWeekData(Models\Course $course, Carbon $reportingDate = null)
    {
        if ($reportingDate === null) {
            $reportingDate = LocalReport::getReportingDate($course->center);
        } else {
            App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);
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

    /**
     * Stash information about a registration (combined name data and course progress data) to be used for later validation.
     * @param  Center $center   The courses's center
     * @param  Carbon           $reportingDate Reporting date
     * @param  array            $data          Information to use to construct a Course.
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $submissionData = App::make(SubmissionData::class);
        $courseId = array_get($data, 'id', null);
        if (is_numeric($courseId)) {
            $courseId = intval($courseId);
        }

        if ($courseId !== null && $courseId > 0) {
            $courseModel = Models\Course::findOrFail($courseId);
            $course = Domain\Course::fromModel(null, $courseModel);

            $course->updateFromArray($data, [
                'startDate',
                'type',
                'quarterStartTer',
                'quarterStartStandardStarts',
                'quarterStartXfer',
                'currentTer',
                'currentStandardStarts',
                'currentXfer',
            ]);
        } else {
            if (!$courseId) {
                $courseId = $submissionData->generateId();
                $data['id'] = $courseId;
            }
            $course = Domain\Course::fromArray($data);
        }
        $submissionData->store($center, $reportingDate, $course);

        $report = LocalReport::getStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $course, $courseId);

        return [
            'success' => true,
            'storedId' => $courseId,
            'meta' => $this->getCourseMeta($course, $center, $reportingDate),
            'valid' => $validationResults['valid'],
            'messages' => $validationResults['messages'],
        ];
    }

    /**
     * Commit week data to the database. Will be performed during validation to write the domain object into the DB
     * @param  Models\Course  $application   The application we are working with.
     * @param  Carbon         $reportingDate [description]
     * @param  Domain\Course  $data          [description]
     */
    public function commitStashedApp(Models\Course $course, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $courseData = $this->getWeekData($course, $reportingDate);

        $courseDomain = Domain\Course::fromModel($courseData, $course);
        $courseDomain->courseId = $course->id;
        $courseDomain->clearSetValues();

        // Now insert our newly changed data, validating and coercing too
        $courseDomain->updateFromArray($data);
        $courseDomain->fillModel($courseData, $course);

        $courseData->save();

        return $courseData->load('course', 'course.center', 'statsReport');
    }

    protected function getCourseMeta(Domain\Course $course, Models\Center $center, Carbon $reportingDate)
    {
        $isFirstWeek = Models\Quarter::isFirstWeek($center->region);

        // TODO: fix this so we don't assume the course starts on a Saturday
        $courseReportDate = $course->startDate->copy()->addDays(6);
        $isPastCourse = $reportingDate->gt($courseReportDate);
        $isCompletionWeek = $reportingDate->eq($courseReportDate);

        $meta = $course->meta;
        $meta['canEditQuarterStart'] = ($course->id < 0 || $isFirstWeek);
        $meta['canEditGuestGame'] = !$isPastCourse;
        $meta['canEditCurrent'] = !$isPastCourse;
        $meta['canEditCompletion'] = $isCompletionWeek;
        $meta['isPastCourse'] = $isPastCourse;
        $meta['isCompletionReportWeek'] = $isCompletionWeek;

        return $meta;
    }
}
