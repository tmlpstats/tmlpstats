<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain;

class Scoreboard extends AuthenticatedApiBase
{
    public function allForCenter(Models\Center $center, Carbon $reportingDate, $includeInProgress = false)
    {
        $localReport = App::make(LocalReport::class);
        $statsReport = $localReport->getLastStatsReportSince($center, $reportingDate);
        $weeks = $localReport->getQuarterScoreboard($statsReport, ['returnObject' => true]);

        if ($includeInProgress) {
            $submissionData = App::make(SubmissionData::class);
            $found = $submissionData->allForType($center, $reportingDate, Domain\Scoreboard::class);
            foreach ($found as $scoreboard) {
                $v = $weeks->ensureWeek($scoreboard->week);
                // laziest way to do this is to simply fill it with the array
                $v->parseArray($scoreboard->toArray());
                $v->meta['localChanges'] = true;
            }
        }

        // fill some additional metadata
        $quarter = $statsReport->quarter;

        $week = $quarter->getQuarterStartDate($center)->addWeek();
        $endDate = $quarter->getQuarterEndDate($center);
        $classrooms = [
            $quarter->getClassroom1Date($center),
            $quarter->getClassroom2Date($center),
            $quarter->getClassroom3Date($center),
        ];
        $weekNumber = 0;
        while ($week->lte($endDate)) {
            $scoreboard = $weeks->ensureWeek($week);
            $scoreboard->meta['weekNum'] = ++$weekNumber;
            foreach ($classrooms as $classroomDate) {
                // TODO deal with non-friday classroom scenarios
                if ($classroomDate->eq($week)) {
                    $scoreboard->meta['isClassroom'] = true;
                }
            }
            $scoreboard->meta['canEditPromise'] = false;
            $scoreboard->meta['canEditActual'] = ($week->toDateString() == $reportingDate->toDateString());

            $week->addWeek();
        }

        $output = [];
        foreach ($weeks->sortedValues() as $scoreboard) {
            $output[] = $scoreboard->toNewArray();
        }

        return $output;
    }

    public function stash(Models\Center $center, Carbon $reportingDate, $data)
    {
        $r1 = App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);
        if (!$r1['success']) {
            return $r1;
        }
        $scoreboard = Domain\Scoreboard::fromArray($data);
        $submissionData = App::make(SubmissionData::class);
        $submissionData->store($center, $reportingDate, $scoreboard);

        return ['success' => true];
    }
}
