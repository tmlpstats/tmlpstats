<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Domain;

class ApiCenterGamesValidator extends ApiValidatorAbstract
{
    protected $reportedActuals = [];

    protected function validate($data)
    {
        $reportedActuals = [];
        $ref = null;

        foreach ($data['scoreboard'] as $scoreboard) {
            if ($scoreboard->week->eq($this->reportingDate)) {
                $weekData = $scoreboard->toArray();

                $reportedActuals = $weekData['actual'];
                $ref = $scoreboard->getReference(['game' => '', 'promiseType' => 'actual']);
                break;
            }
        }

        if (!$reportedActuals) {
            // if there's no scoreboard data to validate, skip
            return $this->isValid;
        }

        if (!$this->validateCourses($data, $reportedActuals, $ref)) {
            $this->isValid = false;
        }

        if (!$this->validateTeamExpansion($data, $reportedActuals, $ref)) {
            $this->isValid = false;
        }

        if (!$this->validateGitw($data, $reportedActuals, $ref)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateGame($game, $reported, $calculated, $ref)
    {
        if ($reported != $calculated) {
            $this->addMessage('error', [
                'id' => strtoupper("CENTERGAME_{$game}_ACTUAL_INCORRECT"),
                'ref' => array_merge($ref, ['game' => $game]),
                'params' => [
                    'reported' => $reported,
                    'calculated' => $calculated,
                ],
            ]);
            return false;
        }

        return true;
    }

    public function validateCourses($data, $reportedActuals, $ref)
    {
        $isValid = true;

        $calculated = $this->calculateCourseRegistrations($data['course']);

        foreach (['cap', 'cpc'] as $game) {
            if (!$this->validateGame($game, $reportedActuals[$game], $calculated[$game], $ref)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTeamExpansion($data, $reportedActuals, $ref)
    {
        $isValid = true;

        $calculated = $this->calculateCourseRegistrations($data['teamApplication']);

        foreach (['t1x', 't2x'] as $game) {
            if (!$this->validateGame($game, $reportedActuals[$game], $calculated[$game], $ref)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateGitw($data, $reportedActuals, $ref)
    {
        $isValid = true;

        $calculated = $this->calculateGitw($data['teamMember']);

        return $this->validateGame('gitw', $reportedActuals['gitw'], $calculated, $ref);
    }

    protected function calculateGitw($teamMemberData)
    {
        $activeMemberCount = 0;
        $effectiveCount = 0;

        foreach ($teamMemberData as $member) {
            if ($member->withdrawCodeId || $member->xferOut) {
                continue;
            }

            $activeMemberCount++;
            if ($member->gitw) {
                $effectiveCount++;
            }
        }

        $gitwGame = 0;
        if ($activeMemberCount) {
            $gitwGame = round(($effectiveCount / $activeMemberCount) * 100);
        }

        return $gitwGame;
    }

    protected function calculateCourseRegistrations($courseData)
    {
        $capCurrentStandardStarts = 0;
        $capQStartStandardStarts = 0;
        $cpcCurrentStandardStarts = 0;
        $cpcQStartStandardStarts = 0;

        foreach ($courseData as $course) {
            if ($course->type == 'CAP') {
                $capCurrentStandardStarts += $course->currentStandardStarts;
                $capQStartStandardStarts += $course->quarterStartStandardStarts;
            } else if ($course->type == 'CPC') {
                $cpcCurrentStandardStarts += $course->currentStandardStarts;
                $cpcQStartStandardStarts += $course->quarterStartStandardStarts;
            }
        }

        return [
            'cap' => $capCurrentStandardStarts - $capQStartStandardStarts,
            'cpc' => $cpcCurrentStandardStarts - $cpcQStartStandardStarts,
        ];
    }

    protected function calculateTeamApplicationApprovals($teamApplicationData)
    {
        $t1CurrentApproved = 0;
        $t2CurrentApproved = 0;
        $t1QStartApproved = 0;
        $t2QStartApproved = 0;

        foreach ($teamApplicationData as $registration) {
            if (!$registration->apprDate) {
                continue;
            }

            if ($registration->teamYear == 1) {
                $t1CurrentApproved++;
            } else {
                $t2CurrentApproved++;
            }
        }

        $applicationData = $this->getQuarterStartingApplications();
        foreach ($applicationData as $app) {
            // TODO: How should withdrawn at the weekend be reported?
            if (!$app->apprDate || $app->apprDate->gt($this->reportingDate)) {
                continue;
            }

            if ($app->registration->teamYear == 1) {
                $t1QStartApproved++;
            } else {
                $t2QStartApproved++;
            }
        }

        return [
            't1x' => $t1CurrentApproved - $t1QStartApprovedd,
            't2x' => $t2CurrentApproved - $t2QStartApproved,
        ];
    }

    protected function getQuarterStartingApplications()
    {
        $centerQuarter = Domain\CenterQuarter::fromModel($this->center, $this->quarter)
            ->toArray();

        $firstReport = StatsReport::byCenter($this->center)
            ->reportingDate($centerQuarter['firstWeekDate'])
            ->official()
            ->first();

        return $firstReport->tmlpRegistrationData();
    }
}
