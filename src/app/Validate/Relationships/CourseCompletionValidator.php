<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats as Models;
use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Validate\ValidatorAbstract;

class CourseCompletionValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function validate($data)
    {
        if (!$this->validateCapCompletion($data)) {
            $this->isValid = false;
        }
        if (!$this->validateCpcCompletion($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateCapCompletion($data)
    {
        $isValid = true;

        $lastWeekDate = $this->reportingDate->copy()->subWeek();

        $coursesCompleted = array();

        $courses = $data['commCourseInfo'];
        $completionRegistrations = 0;
        $cpcCurrentStandardStarts = 0;
        $cpcQStartStandardStarts  = 0;
        foreach ($courses as $course) {
            if ($course['type'] == 'CAP'
                && $course['startDate']->gt($lastWeekDate)
                && $course['startDate']->lt($this->reportingDate)
            ) {
                $completionRegistrations += $course['registrations'];
                $coursesCompleted[] = $course;
            }
            if ($course['type'] == 'CPC') {
                $cpcCurrentStandardStarts += $course['currentStandardStarts'];
                $cpcQStartStandardStarts += $course['quarterStartStandardStarts'];
            }
        }

        if (!$coursesCompleted) {
            return $isValid;
        }

        // Ignore reports from last quarter here because there is no "last week" value for
        // the first week of the quarter.
        $lastWeeksReport = Models\StatsReport::byCenter($this->center)
            ->byQuarter($this->quarter)
            ->reportingDate($lastWeekDate)
            ->official()
            ->first();

        $lastWeeksCpc = 0;
        if ($lastWeeksReport) {
            $lastWeeksActuals = $lastWeeksReport->centerStatsData()
                ->actual()
                ->reportingDate($lastWeekDate)
                ->first();

            if ($lastWeeksActuals) {
                $lastWeeksCpc = $lastWeeksActuals->cpc;
            }
        }

        // Registrations from the last week
        $cpcRegistrations = ($cpcCurrentStandardStarts - $cpcQStartStandardStarts) - $lastWeeksCpc;

        if ($completionRegistrations > $cpcRegistrations) {
            $this->addMessage('IMPORTDOC_CAP_REG_CPC_REG_MISMATCH', $completionRegistrations, $cpcRegistrations);
        }

        return $isValid;
    }

    public function validateCpcCompletion($data)
    {
        $isValid = true;

        $lastWeekDate = $this->reportingDate->copy()->subWeek();

        $courses = $data['commCourseInfo'];
        $courseRegistrations = 0;
        foreach ($courses as $course) {
            if ($course['type'] == 'CPC'
                && $course['startDate']->gt($lastWeekDate)
                && $course['startDate']->lt($this->reportingDate)
            ) {
                $courseRegistrations += $course['registrations'];
                $coursesCompleted[] = $course;
            }
        }

        if (!$coursesCompleted) {
            return $isValid;
        }

        $registrations = $data['tmlpRegistration'];
        $appRegistrations = 0;
        foreach ($registrations as $registration) {
            if ($registration['incomingTeamYear'] == 1
                && $registration['regDate']->gt($lastWeekDate)
            ) {
                $appRegistrations++;
            }
        }

        if ($courseRegistrations > $appRegistrations) {
            $this->addMessage('IMPORTDOC_CPC_REG_T1_APP_MISMATCH', $courseRegistrations, $appRegistrations);
        }

        return $isValid;
    }
}
