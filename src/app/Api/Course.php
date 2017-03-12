<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;

/**
 * Courses
 */
class Course extends ApiBase
{
    private function relevantReport(Models\Center $center, Carbon $reportingDate)
    {
        $crd = Encapsulations\CenterReportingDate::ensure($center, $reportingDate);
        $quarter = $crd->getQuarter();

        return Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->official()
            ->where('reporting_date', '<=', $reportingDate)
            ->orderBy('reporting_date', 'desc')
            ->first();
    }

    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $allCourses = [];
        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\Course::class);
            foreach ($found as $domain) {
                $allCourses[$domain->id] = $domain;
                $domain->meta['localChanges'] = true;
            }
        }

        $lastReport = $this->relevantReport($center, $reportingDate);
        if ($lastReport) {
            foreach (App::make(LocalReport::class)->getCourseList($lastReport) as $cd) {
                // it's a small optimization, but prevent creating domain if we have an existing SubmissionData version
                if (isset($allCourses[$cd->courseId])) {
                    continue;
                }

                $domain = Domain\Course::fromModel($cd, $cd->course);
                $domain->meta['fromReport'] = true;
                $allCourses[$domain->id] = $domain;
            }
        }

        return array_values($allCourses);
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
        $courseId = $submissionData->numericStorageId($data, 'id');

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
            $course = Domain\Course::fromArray($data);
        }
        $report = LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $course, $courseId);

        if (!isset($data['_idGenerated']) || $validationResults['valid']) {
            $submissionData->store($center, $reportingDate, $course);
        } else {
            return [
                'success' => false,
                'valid' => $validationResults['valid'],
                'messages' => $validationResults['messages'],
            ];
        }

        return [
            'success' => true,
            'storedId' => $courseId,
            'meta' => $this->getCourseMeta($course, $center, $reportingDate),
            'valid' => $validationResults['valid'],
            'messages' => $validationResults['messages'],
        ];
    }

    /**
     * TODO implement me. This is copypasta from application.
     * @param  Models\Course  $course
     * @param  Carbon         $reportingDate [description]
     * @param  Domain\Course  $data          [description]
     */
    public function commitStashedCourse(Models\Course $course, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        //TODO get from submissionData
        //$courseData = $this->getWeekData($course, $reportingDate);

        $courseDomain = Domain\Course::fromModel($courseData, $course);
        $courseDomain->courseId = $course->id;
        $courseDomain->clearSetValues();

        // Now insert our newly changed data, validating and coercing too
        $courseDomain->updateFromArray($data);
        $courseDomain->fillModel($courseData, $course);

        $courseData->save();

        return $courseData->load('course', 'course.center', 'statsReport');
    }

    public function getWeekSoFar(Models\Center $center, Carbon $reportingDate)
    {
        return $this->allForCenter($center, $reportingDate, true);
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
