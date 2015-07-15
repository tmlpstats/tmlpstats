<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Import\Xlsx\Reader as Reader;
use Respect\Validation\Validator as v;

class StatsReportValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function populateValidators($data)
    {
        $intNotNullValidator    = v::when(v::nullValue(), v::alwaysInvalid(), v::int());
        $percentValidator       = v::numeric()->between(0, 100, true);
        $percentOrNullValidator = v::when(v::nullValue(), v::alwaysValid(), $percentValidator);

        $types = array('promise', 'actual');

        $this->dataValidators['reportingDate']        = v::date('Y-m-d');
        $this->dataValidators['type']                 = v::in($types);
        $this->dataValidators['tdo']                  = $percentOrNullValidator;
        $this->dataValidators['cap']                  = $intNotNullValidator;
        $this->dataValidators['cpc']                  = $intNotNullValidator;
        $this->dataValidators['t1x']                  = $intNotNullValidator;
        $this->dataValidators['t2x']                  = $intNotNullValidator;
        $this->dataValidators['gitw']                 = $percentValidator;
        $this->dataValidators['lf']                   = $intNotNullValidator;
    }

    protected function validate($data)
    {
        return $this->isValid;
    }















    protected function validateTeamExpansion()
    {
        $isValid = true;

        $this->sheetId = ImportDocument::TAB_COURSES;

        $qStartCurrentTeam1Registered = 0;
        $qStartFutureTeam1Registered  = 0;
        $qStartCurrentTeam1Approved   = 0;
        $qStartFutureTeam1Approved    = 0;

        $qStartCurrentTeam2Registered = 0;
        $qStartFutureTeam2Registered  = 0;
        $qStartCurrentTeam2Approved   = 0;
        $qStartFutureTeam2Approved    = 0;

        $tmlpRegistrations = $this->importers['tmlpRegistration']->getData();
        foreach ($tmlpRegistrations as $registration) {

            $regDate = Util::parseUnknownDateFormat($registration['regDate']);
            if ($registration['incomingWeekend'] == 'current') {

                if ($regDate && $regDate->lte($this->quarter->startWeekendDate)) {
                    if ($registration['incomingTeamYear'] == 1) {
                        $qStartCurrentTeam1Registered++;
                    } else {
                        $qStartCurrentTeam2Registered++;
                    }
                }
            } else {

                if ($regDate && $regDate->lte($this->quarter->startWeekendDate)) {
                    if ($registration['incomingTeamYear'] == 1) {
                        $qStartFutureTeam1Registered++;
                    } else {
                        $qStartFutureTeam2Registered++;
                    }
                }
            }

            if ($registration['apprDate']) {
                $apprDate = Util::parseUnknownDateFormat($registration['apprDate']);
                if ($registration['incomingWeekend'] == 'current') {

                    if ($apprDate && $apprDate->lte($this->quarter->startWeekendDate)) {
                        if ($registration['incomingTeamYear'] == 1) {
                            $qStartCurrentTeam1Approved++;
                        } else {
                            $qStartCurrentTeam2Approved++;
                        }
                    }
                } else {

                    if ($apprDate && $apprDate->lte($this->quarter->startWeekendDate)) {
                        if ($registration['incomingTeamYear'] == 1) {
                            $qStartFutureTeam1Approved++;
                        } else {
                            $qStartFutureTeam2Approved++;
                        }
                    }
                }
            }
        }

        $quarterStart = clone $this->quarter->startWeekendDate;
        $firstWeekDate = $quarterStart->addDays(7);

        $qStartTotalTeam1Registered = 0;
        $qStartTotalTeam1Approved   = 0;
        $qStartTotalTeam2Registered = 0;
        $qStartTotalTeam2Approved   = 0;

        $tmlpGames = $this->importers['tmlpCourseInfo']->getData();
        foreach ($tmlpGames as $game) {

            $calculatedRegistered = 0;
            $calculatedApproved   = 0;

            switch ($game['type']) {

                case 'Incoming T1':
                    $calculatedRegistered        = $qStartCurrentTeam1Registered;
                    $calculatedApproved          = $qStartCurrentTeam1Approved;

                    $qStartTotalTeam1Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam1Approved   += $game['quarterStartApproved'];
                    break;
                case 'Future T1':
                    $calculatedRegistered        = $qStartFutureTeam1Registered;
                    $calculatedApproved          = $qStartFutureTeam1Approved;

                    $qStartTotalTeam1Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam1Approved   += $game['quarterStartApproved'];
                    break;
                case 'Incoming T2':
                    $calculatedRegistered        = $qStartCurrentTeam2Registered;
                    $calculatedApproved          = $qStartCurrentTeam2Approved;

                    $qStartTotalTeam2Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam2Approved   += $game['quarterStartApproved'];
                    break;
                case 'Future T2':
                    $calculatedRegistered        = $qStartFutureTeam2Registered;
                    $calculatedApproved          = $qStartFutureTeam2Approved;

                    $qStartTotalTeam2Registered += $game['quarterStartRegistered'];
                    $qStartTotalTeam2Approved   += $game['quarterStartApproved'];
                    break;
                // default: ignore (only with corrupt sheet). An error is reported already
            }

            // Validate Quarter starting totals on the first week
            if ($this->reportingDate->eq($firstWeekDate)) {

                if ($game['quarterStartRegistered'] != $calculatedRegistered) {
                    $this->addMessage('IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $game['type'], $game['quarterStartRegistered'], $calculatedRegistered);
                    $isValid = false;
                }

                if ($game['quarterStartApproved'] != $calculatedApproved) {
                    $this->addMessage('IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $game['type'], $game['quarterStartApproved'], $calculatedApproved);
                    $isValid = false;
                }
            }
        }

        // Validate Quarter starting totals on mid quarter weeks. (Registrations may move between current and future)
        if ($this->reportingDate->ne($firstWeekDate)) {

            $calculatedTotals = $qStartCurrentTeam1Registered + $qStartFutureTeam1Registered;
            if ($qStartTotalTeam1Registered != $calculatedTotals) {
                $this->addMessage('IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $qStartTotalTeam1Registered, $calculatedTotals);
            }

            $calculatedTotals = $qStartCurrentTeam1Approved + $qStartFutureTeam1Approved;
            if ($qStartTotalTeam1Approved != $calculatedTotals) {
                $this->addMessage('IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $qStartTotalTeam1Approved, $calculatedTotals);
            }

            $calculatedTotals = $qStartCurrentTeam2Registered + $qStartFutureTeam2Registered;
            if ($qStartTotalTeam2Registered != $calculatedTotals) {
                $this->addMessage('IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $qStartTotalTeam2Registered, $calculatedTotals);
            }

            $calculatedTotals = $qStartCurrentTeam2Approved + $qStartFutureTeam2Approved;
            if ($qStartTotalTeam2Approved != $calculatedTotals) {
                $this->addMessage('IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $qStartTotalTeam2Approved, $calculatedTotals);
            }
        }

        return $isValid;
    }

    protected function validateCenterGames()
    {
        $isValid = true;
        $thisWeekActual = null;

        $this->sheetId = ImportDocument::TAB_WEEKLY_STATS;

        $centerStats = $this->importers['centerStats']->getData();

        foreach ($centerStats as $week) {

            if ($week['type'] == 'actual'
                && $week['reportingDate'] == $this->reportingDate->toDateString()) {

                $thisWeekActual = $week;
                break;
            }
        }

        // GITW and TDO
        $classList = $this->importers['classList']->getData();
        $activeMemberCount = 0;
        $effectiveCount    = 0;
        $tdoCount          = 0;

        foreach ($classList as $member) {

            if ($member['wd'] || $member['wbo'] || $member['xferOut']) {
                continue;
            }

            $activeMemberCount++;
            if (preg_match('/^E$/i', $member['gitw'])) {
                $effectiveCount++;
            }
        }

        $gitwGame = $activeMemberCount ? round(($effectiveCount/$activeMemberCount) * 100) : 0;

        // CAP & CPC Game
        $courses = $this->importers['commCourseInfo']->getData();
        $capCurrentStandardStarts = 0;
        $capQStartStandardStarts  = 0;
        $cpcCurrentStandardStarts = 0;
        $cpcQStartStandardStarts  = 0;

        foreach ($courses as $course) {
            if ($course['type'] == 'CAP') {
                $capCurrentStandardStarts += $course['currentStandardStarts'];
                $capQStartStandardStarts  += $course['quarterStartStandardStarts'];
            } else if ($course['type'] == 'CPC') {
                $cpcCurrentStandardStarts += $course['currentStandardStarts'];
                $cpcQStartStandardStarts  += $course['quarterStartStandardStarts'];
            }
        }

        $capGame = $capCurrentStandardStarts - $capQStartStandardStarts;
        $cpcGame = $cpcCurrentStandardStarts - $cpcQStartStandardStarts;

        // T1x and T2x Games
        $registrations = $this->importers['tmlpRegistration']->getData();
        $t1CurrentApproved = 0;
        $t2CurrentApproved  = 0;
        foreach ($registrations as $registration) {
            if ($registration['appr'] && $registration['incomingTeamYear'] == 1) {
                $t1CurrentApproved++;
            } else if ($registration['appr']) {
                $t2CurrentApproved++;
            }
        }

        $tmlpGames = $this->importers['tmlpCourseInfo']->getData();
        $t1QStartApproved = 0;
        $t2QStartApproved  = 0;
        foreach ($tmlpGames as $game) {
            if (preg_match('/T1/', $game['type'])) {
                $t1QStartApproved += $game['quarterStartApproved'];
            } else if (preg_match('/T2/', $game['type'])) {
                $t2QStartApproved += $game['quarterStartApproved'];
            }
        }

        $t1xGame = $t1CurrentApproved - $t1QStartApproved;
        $t2xGame = $t2CurrentApproved - $t2QStartApproved;

        // Make sure they match
        if ($thisWeekActual) {

            if ($thisWeekActual['cap'] != $capGame) {
                $this->addMessage('IMPORTDOC_CAP_ACTUAL_INCORRECT', $thisWeekActual['cap'], $capGame);
                $isValid = false;
            }

            if ($thisWeekActual['cpc'] != $cpcGame) {
                $this->addMessage('IMPORTDOC_CPC_ACTUAL_INCORRECT', $thisWeekActual['cpc'], $cpcGame);
                $isValid = false;
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
                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function validateStatsReport()
    {
        $isValid = true;

        if ($this->enforceVersion && $this->version != $this->center->sheetVersion) {

            $this->addMessage('IMPORTDOC_SPREADSHEET_VERSION_MISMATCH', $this->statsReport->spreadsheetVersion, $this->center->sheetVersion);
            $isValid = false;
        }

        if ($this->expectedDate && $this->expectedDate->ne($this->statsReport->reportingDate)) {

            if ($this->statsReport->reportingDate->diffInDays($this->statsReport->quarter->endWeekendDate) < 7) {
                // Reporting in the last week of quarter
                if ($this->statsReport->reportingDate->ne($this->statsReport->quarter->endWeekendDate)) {
                    $this->addMessage('IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK', $this->statsReport->reportingDate->toDateString(), $this->statsReport->quarter->endWeekendDate->toDateString());
                    $isValid = false;
                }
            } else {
                $this->addMessage('IMPORTDOC_SPREADSHEET_DATE_MISMATCH', $this->statsReport->reportingDate->toDateString(), $this->expectedDate->toDateString());
                $isValid = false;
            }
        }

        return $isValid;
    }
}
