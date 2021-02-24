<?php
namespace TmlpStats\Api\Submission;

use App;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\SubmissionData;
use TmlpStats\Domain;

class Scoreboard extends AuthenticatedApiBase
{
    const LOCK_SETTING_KEY = 'scoreboardLock';

    public function calculateScoreboard(Models\Center $center, Carbon $reportingDate, $scoreboard)
    {
        $submissionData = App::make(SubmissionData::class);
        $quarter = App::make(Models\Quarter::class)->getCurrentQuarter($center->region);

        $prevWeek = null;
        $currentWeek = null;
        foreach ($scoreboard as $date => $week) {
            if ($week['week'] == $reportingDate->toDateString()) {
                $currentWeek = $week;
                break;
            }
            $prevWeek = $week;
        }


        // calculate CAP, CPC, LF, GITW
        $teamMembers = App::make(Models\Api\TeamMember::class)->allForCenter($center, $reportingDate, true);
        $numTeamMembers =  count($teamMembers);
        foreach ($teamMembers as $item) {
            if ($item->withdrawCodeId || $item->xferOut || $item->wbo) {
                $numTeamMembers--;
            }
        }

        $cap = 0;
        $cpc = 0;
        $lf = 0;
        $gitw = 0;

        $teamMemberData = $submissionData->allForType($center, $reportingDate, Domain\TeamMember::class);
        foreach ($teamMemberData as $item) {

            if ($item->withdrawCodeId || $item->xferOut || $item->wbo) {
                continue;
            }

            if ($item->rppCap != null) {
                $cap += $item->rppCap;
            }
            if ($item->rppCpc != null) {
                $cpc += $item->rppCpc;
            }
            if ($item->rppLf != null) {
                $lf += $item->rppLf;
            }
            if ($item->gitw != null && $item->gitw == 1) {
                $gitw++;
            }

        }

        if ($prevWeek != null) {
            $cap += $prevWeek['games']['cap']['actual'];
            $cpc += $prevWeek['games']['cpc']['actual'];
            $lf += $prevWeek['games']['lf']['actual'];
        }

        $currentWeek['games']['cap']['actual'] = $cap;
        $currentWeek['games']['cpc']['actual'] = $cpc;
        $currentWeek['games']['lf']['actual'] = $lf;
        $currentWeek['games']['gitw']['actual'] = round($gitw/$numTeamMembers * 100);


        // calculate T1X, T2X
        $applications = App::make(Models\Api\Application::class)->allForCenter($center, $reportingDate, true);      // get all applications for center for the quarter


        $t1x = 0;
        $t2x = 0;

        foreach ($applications as $application)
        {
            if($application->apprDate != null) {

                if ($application->apprDate > $quarter->getQuarterStartDate() && $application->apprDate <= $quarter->getQuarterEndDate()) {
                    if ($application->teamYear == 1) {
                        $t1x++;
                    } else {
                        $t2x++;
                    }
                }

                if($application->wdDate != null && $application->wdDate > $quarter->getQuarterStartDate() && $application->wdDate <= $quarter->getQuarterEndDate()) {
                    if($application->teamYear == 1) {
                        $t1x--;
                    } else {
                        $t2x--;
                    }
                }

            }


        }

        $currentWeek['games']['t1x']['actual'] = $t1x;
        $currentWeek['games']['t2x']['actual'] = $t2x;


        $this->stash($center, $reportingDate, $currentWeek);

        return $this->allForCenter($center, $reportingDate, false);

    }

    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false, $returnObject = false)
    {
        $this->assertAuthz($this->context->can('viewSubmissionUi', $center));
        $submissionCore = App::make(Api\SubmissionCore::class);
        $submissionCore->checkCenterDate($center, $reportingDate);


        $localReport = App::make(Api\LocalReport::class);
        $rq = $submissionCore->reportAndQuarter($center, $reportingDate);
        $quarter = $rq['quarter'];
        $statsReport = $rq['report'];
        $reportingDates = $quarter->getCenterQuarter($center)->listReportingDates();

        if ($statsReport !== null) {
            $weeks = $localReport->getQuarterScoreboard($statsReport);
        } else {
            // This should only happen on the first week of the quarter, but we want to initialize the weeks fully.
            $weeks = new Domain\ScoreboardMultiWeek();
            foreach ($reportingDates as $d) {
                $weeks->ensureWeek($d);
            }
        }

        // fill some additional metadata
        $locks = $this->getScoreboardLockQuarter($center, $quarter);

        $classrooms = [
            $quarter->getClassroom1Date($center),
            $quarter->getClassroom2Date($center),
            $quarter->getClassroom3Date($center),
        ];
        $weekNumber = 0;
        foreach ($reportingDates as $week) {
            $scoreboard = $weeks->ensureWeek($week);
            $scoreboard->meta['weekNum'] = ++$weekNumber;
            foreach ($classrooms as $classroomDate) {
                // TODO deal with non-friday classroom scenarios
                if ($classroomDate->eq($week)) {
                    $scoreboard->meta['isClassroom'] = true;
                }
            }
            $weekLock = $locks->getWeekDefault($reportingDate, $week);
            $scoreboard->meta['canEditPromise'] = $weekLock->editPromise;
            $scoreboard->meta['canEditActual'] = $weekLock->editActual || ($week->toDateString() == $reportingDate->toDateString());
        }

        if ($includeInProgress) {
            $submissionData = App::make(Api\SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\Scoreboard::class);
            foreach ($found as $stashed) {
                $scoreboard = $weeks->ensureWeek($stashed->week);
                if ($scoreboard->game('cap')->promise() !== null && !$scoreboard->meta['canEditPromise']) {
                    // If we can't edit promises, only copy actuals.
                    $stashed->eachGame(function ($game) use ($scoreboard) {
                        $scoreboard->setValue($game->key, 'actual', $game->actual());
                    });
                    $scoreboard->meta['mergedLocal'] = true; // mostly as a useful value in tests
                } else {
                    // laziest way to do this is to simply fill it with the array
                    $scoreboard->parseArray($stashed->toArray());
                }
                $scoreboard->meta['localChanges'] = true;
            }
        }

        if ($returnObject) {
            return $weeks;
        }

        $output = [];
        foreach ($weeks->sortedValues() as $scoreboard) {
            $output[] = $scoreboard->toNewArray();
        }

        return $output;
    }

    public function stash(Models\Center $center, Carbon $reportingDate, $data)
    {
        $this->assertAuthz($this->context->can('submitStats', $center), 'User not allowed access to submit this report');
        App::make(Api\SubmissionCore::class)->checkCenterDate($center, $reportingDate, ['write']);

        $scoreboard = Domain\Scoreboard::fromArray($data);
        $submissionData = App::make(Api\SubmissionData::class);
        $submissionData->store($center, $reportingDate, $scoreboard);

        $report = Api\LocalReport::ensureStatsReport($center, $reportingDate);
        $validationResults = App::make(Api\ValidationData::class)->validate($center, $reportingDate);

        $messages = [];
        if (isset($validationResults['messages']['Scoreboard'])) {
            $weekString = $scoreboard->week->toDateString();
            foreach ($validationResults['messages']['Scoreboard'] as $message) {
                if ($message->reference()['id'] == $weekString) {
                    $messages[] = $message;
                }
            }
        }

        return [
            'success' => true,
            'valid' => $validationResults['valid'],
            'messages' => $messages,
            'week' => $scoreboard->week->toDateString(),
        ];
    }

    public function getScoreboardLockQuarter(Models\Center $center, Models\Quarter $quarter)
    {
        $v = $this->context->getSetting(static::LOCK_SETTING_KEY, $center, $quarter);
        if ($v === null) {
            // Create a blank scoreboard lock with reporting dates filled
            $quarter->setRegion($center->region);
            $reportingDates = $quarter->getCenterQuarter($center)->listReportingDates();

            return new Domain\ScoreboardLockQuarter($reportingDates);
        } else {
            return Domain\ScoreboardLockQuarter::fromArray($v);
        }
    }

    public function setScoreboardLockQuarter(Models\Center $center, Models\Quarter $quarter, $data)
    {
        $this->assertCan('adminScoreboard', $center);
        $locks = Domain\ScoreboardLockQuarter::fromArray($data);
        Models\Setting::upsert([
            'name' => static::LOCK_SETTING_KEY,
            'center' => $center,
            'quarter' => $quarter,
            'value' => $locks->toArray(),
        ]);
    }

    public function getWeekSoFar(Models\Center $center, Carbon $reportingDate, $includeInProgress = true)
    {
        $results = [];

        $allData = $this->allForCenter($center, $reportingDate, $includeInProgress);
        foreach ($allData as $dataArr) {
            $meta = array_get($dataArr, 'meta', []);
            $dataObject = Domain\Scoreboard::fromArray($dataArr);

            if (array_get($meta, 'canEditPromise', false) || array_get($meta, 'canEditActual', false)) {
                $results[] = $dataObject;
            }
        }

        return $results;
    }
}
