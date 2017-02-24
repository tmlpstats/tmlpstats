<?php
namespace TmlpStats\Validate\Relationships;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;
use TmlpStats\Validate\ApiValidatorAbstract;

class ApiCenterGamesValidator extends ApiValidatorAbstract
{
    protected function validate($data)
    {
        $reportedActuals = null;
        $ref = null;

        foreach ($data['Scoreboard'] as $scoreboard) {
            if ($scoreboard->week->eq($this->reportingDate)) {
                $weekData = $scoreboard->toArray();

                $reportedActuals = $weekData['actual'];
                $ref = $scoreboard->getReference(['game' => '', 'promiseType' => 'actual']);
                break;
            }
        }

        if (!$reportedActuals) {
            // if there's no scoreboard data to validate, fail
            return false;
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

    protected function validateGame($game, $reported, $calculated, $ref)
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

    protected function validateCourses($data, $reportedActuals, $ref)
    {
        $isValid = true;

        $calculated = $this->calculateCourseRegistrations($data['Course']);

        foreach (['cap', 'cpc'] as $game) {
            if (!$this->validateGame($game, $reportedActuals[$game], $calculated[$game], $ref)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function validateTeamExpansion($data, $reportedActuals, $ref)
    {
        $isValid = true;

        $calculated = $this->calculateTeamApplicationApprovals($data['TeamApplication']);

        foreach (['t1x', 't2x'] as $game) {
            if (!$this->validateGame($game, $reportedActuals[$game], $calculated[$game], $ref)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function validateGitw($data, $reportedActuals, $ref)
    {
        $isValid = true;

        $calculated = $this->calculateGitw($data['TeamMember']);

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

        foreach ($teamApplicationData as $app) {
            if ($app->withdrawCodeId !== null
                || !$app->apprDate
                || $app->apprDate->gt($this->reportingDate)
            ) {
                continue;
            }

            if ($app->teamYear == 1) {
                $t1CurrentApproved++;
            } else {
                $t2CurrentApproved++;
            }
        }

        $approvedCounts = $this->getQuarterStartingApprovedCounts();

        return [
            't1x' => $t1CurrentApproved - $approvedCounts['t1x'],
            't2x' => $t2CurrentApproved - $approvedCounts['t2x'],
        ];
    }

    protected function getQuarterStartingApprovedCounts()
    {
        // toArray is done here to deal with an interesting issue with parsing dates before 1900
        $centerQuarter = Domain\CenterQuarter::ensure($this->center, $this->quarter)->toArray();

        $counts = ['t1x' => 0, 't2x' => 0];

        $startWeekendDate = Carbon::parse($centerQuarter['startWeekendDate']);

        $firstReport = Models\StatsReport::byCenter($this->center)
            ->reportingDate($startWeekendDate->copy()->addWeek())
            ->official()
            ->first();

        if ($firstReport) {
            $items = $firstReport
                ->tmlpRegistrationData()
                ->where('appr_date', '<=', $startWeekendDate)
                ->get()
                ->map(function ($app) {
                    return $app->registration->teamYear;
                });
        } else {
            // When it's the first week, we don't have counts, so use the stash data.
            $result = App::make(Api\Application::class)->allForCenter($this->center, $this->reportingDate, true);

            $items = collect($result)->filter(function ($teamApp) use ($startWeekendDate) {
                return $teamApp->apprDate !== null && $teamApp->apprDate->lte($startWeekendDate);
            })->map(function ($teamApp) {
                return $teamApp->teamYear;
            });
        }

        foreach ($items->all() as $teamYear) {
            $counts["t${teamYear}x"] += 1;
        }

        return $counts;
    }
}
