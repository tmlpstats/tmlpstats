<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Traits;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;

/**
 * Courses
 */
class Course extends ApiBase
{
    use Traits\UsesReportDates, Traits\ValidatesObjects;

    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);

        $allCourses = [];
        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\Course::class);
            foreach ($found as $domain) {
                $allCourses[$domain->id] = $domain;
                $domain->meta = $this->getCourseMeta($domain, $center, $reportingDate);
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
                $domain->meta = $this->getCourseMeta($domain, $center, $reportingDate);
                $domain->meta['fromReport'] = true;
                $allCourses[$domain->id] = $domain;
            }
        }

        return $allCourses;
    }

    /**
     * Stash information about a registration (combined name data and course progress data) to be used for later validation.
     * @param  Center $center   The courses's center
     * @param  Carbon           $reportingDate Reporting date
     * @param  array            $data          Information to use to construct a Course.
     */
    public function stash(Models\Center $center, Carbon $reportingDate, array $data)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate, ['write']);

        $submissionData = App::make(SubmissionData::class);
        $courseId = $submissionData->numericStorageId($data, 'id');

        $pastWeeks = [];

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

            $pastWeeks = $this->getPastWeeksData($center, $reportingDate, $courseModel);
        } else {
            $course = Domain\Course::fromArray($data);
        }
        $report = LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = $this->validateObject($report, $course, $courseId, $pastWeeks);

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

    public function getWeekSoFar(Models\Center $center, Carbon $reportingDate, $includeInProgress = true)
    {
        return $this->allForCenter($center, $reportingDate, $includeInProgress);
    }

    protected function getPastWeeksData(Models\Center $center, Carbon $reportingDate, Models\Course $course)
    {
        $lastWeekReportingDate = $this->lastReportingDate($center, $reportingDate, true);
        if (!$lastWeekReportingDate) {
            return [];
        }

        $lastReport = $this->relevantReport($center, $lastWeekReportingDate);
        if (!$lastReport) {
            return [];
        }

        $lastWeekData = Models\CourseData::byStatsReport($lastReport)->byCourse($course)->first();
        if (!$lastWeekData) {
            return [];
        }

        return [
            Domain\Course::fromModel($lastWeekData, $course),
        ];
    }

    protected function getCourseMeta(Domain\Course $course, Models\Center $center, Carbon $reportingDate)
    {
        $cq = Encapsulations\CenterReportingDate::ensure($center, $reportingDate)->getCenterQuarter();

        $isFirstWeek = $reportingDate->eq($cq->firstWeekDate);
        $courseReportDate = $course->startDate->copy()->next(Carbon::FRIDAY);
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
