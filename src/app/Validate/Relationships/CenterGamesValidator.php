<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Validate\ValidatorAbstract;

class CenterGamesValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function validate($data)
    {
        $thisWeekActual = null;

        $centerStats = $data['centerStats'];

        foreach ($centerStats as $week) {

            if ($week['type'] == 'actual'
                && $week['reportingDate'] == $this->reportingDate->toDateString()
            ) {

                $thisWeekActual = $week;
                break;
            }
        }

        // GITW and TDO
        $classList         = $data['classList'];
        $activeMemberCount = 0;
        $effectiveCount    = 0;

        foreach ($classList as $member) {

            if ($member['wd'] || $member['wbo'] || $member['xferOut']) {
                continue;
            }

            $activeMemberCount++;
            if (preg_match('/^E$/i', $member['gitw'])) {
                $effectiveCount++;
            }
        }

        $gitwGame = $activeMemberCount ? round(($effectiveCount / $activeMemberCount) * 100) : 0;

        // CAP & CPC Game
        $courses                  = $data['commCourseInfo'];
        $capCurrentStandardStarts = 0;
        $capQStartStandardStarts  = 0;
        $cpcCurrentStandardStarts = 0;
        $cpcQStartStandardStarts  = 0;

        foreach ($courses as $course) {
            if ($course['type'] == 'CAP') {
                $capCurrentStandardStarts += $course['currentStandardStarts'];
                $capQStartStandardStarts += $course['quarterStartStandardStarts'];
            } else if ($course['type'] == 'CPC') {
                $cpcCurrentStandardStarts += $course['currentStandardStarts'];
                $cpcQStartStandardStarts += $course['quarterStartStandardStarts'];
            }
        }

        $capGame = $capCurrentStandardStarts - $capQStartStandardStarts;
        $cpcGame = $cpcCurrentStandardStarts - $cpcQStartStandardStarts;

        // T1x and T2x Games
        $registrations     = $data['tmlpRegistration'];
        $t1CurrentApproved = 0;
        $t2CurrentApproved = 0;
        foreach ($registrations as $registration) {
            if ($registration['appr'] && $registration['incomingTeamYear'] == 1) {
                $t1CurrentApproved++;
            } else if ($registration['appr']) {
                $t2CurrentApproved++;
            }
        }

        $tmlpGames        = $data['tmlpCourseInfo'];
        $t1QStartApproved = 0;
        $t2QStartApproved = 0;
        foreach ($tmlpGames as $game) {
            if (strpos($game['type'], 'T1') !== false) {
                $t1QStartApproved += $game['quarterStartApproved'];
            } else {
                $t2QStartApproved += $game['quarterStartApproved'];
            }
        }

        $t1xGame = $t1CurrentApproved - $t1QStartApproved;
        $t2xGame = $t2CurrentApproved - $t2QStartApproved;

        // Make sure they match
        if ($thisWeekActual) {

            if ($thisWeekActual['cap'] != $capGame) {
                $this->addMessage('IMPORTDOC_CAP_ACTUAL_INCORRECT', $thisWeekActual['cap'], $capGame);
                $this->isValid = false;
            }

            if ($thisWeekActual['cpc'] != $cpcGame) {
                $this->addMessage('IMPORTDOC_CPC_ACTUAL_INCORRECT', $thisWeekActual['cpc'], $cpcGame);
                $this->isValid = false;
            }

            if ($thisWeekActual['t1x'] != $t1xGame) {
                // This is a warning since the regional is asked to verify
                $this->addMessage('IMPORTDOC_T1X_ACTUAL_INCORRECT', $thisWeekActual['t1x'], $t1xGame);
            }

            if ($thisWeekActual['t2x'] != $t2xGame) {
                $this->addMessage('IMPORTDOC_T2X_ACTUAL_INCORRECT', $thisWeekActual['t2x'], $t2xGame);
            }

            if ($thisWeekActual['gitw'] != $gitwGame) {
                $this->addMessage('IMPORTDOC_GITW_ACTUAL_INCORRECT', $thisWeekActual['gitw'], $gitwGame);
                $this->isValid = false;
            }
        }

        return $this->isValid;
    }
}
