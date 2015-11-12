<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Util;
use TmlpStats\Validate\ValidatorAbstract;

class TeamExpansionValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_COURSES;

    protected function validate($data)
    {
        $calculatedCounts = $this->calculateQuarterStartingCounts($data['tmlpRegistration']);

        $quarterStart = clone $this->quarter->startWeekendDate;
        $firstWeekDate = $quarterStart->addDays(7);

        $reportedCounts = [
            'team1' => [
                'registered' => 0,
                'approved' => 0,
            ],
            'team2' => [
                'registered' => 0,
                'approved' => 0,
            ],
        ];

        $tmlpGames = $data['tmlpCourseInfo'];
        foreach ($tmlpGames as $game) {

            $team = strpos($game['type'], 'T1') !== false
                ? 'team1'
                : 'team2';

            $weekend = strpos($game['type'], 'Incoming') !== false
                ? 'current'
                : 'future';

            $calculatedCount = $calculatedCounts[$team][$weekend];

            $reportedCounts[$team]['registered'] += $game['quarterStartRegistered'];
            $reportedCounts[$team]['approved'] += $game['quarterStartApproved'];

            // Validate Quarter starting totals on the first week
            if ($this->reportingDate->eq($firstWeekDate)) {

                if ($game['quarterStartRegistered'] != $calculatedCount['registered']) {
                    $this->addMessage('IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $game['type'], $game['quarterStartRegistered'], $calculatedCount['registered']);
                    $this->isValid = false;
                }

                if ($game['quarterStartApproved'] != $calculatedCount['approved']) {
                    $this->addMessage('IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $game['type'], $game['quarterStartApproved'], $calculatedCount['approved']);
                    $this->isValid = false;
                }
            }
        }

        // Validate Quarter starting totals on mid quarter weeks. (Registrations may move between current and future)
        if ($this->reportingDate->ne($firstWeekDate)) {

            $calculatedTotal = $calculatedCounts['team1']['current']['registered'] + $calculatedCounts['team1']['future']['registered'];
            if ($reportedCounts['team1']['registered'] != $calculatedTotal) {
                $this->addMessage('IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $reportedCounts['team1']['registered'], $calculatedTotal);
            }

            $calculatedTotal = $calculatedCounts['team1']['current']['approved'] + $calculatedCounts['team1']['future']['approved'];
            if ($reportedCounts['team1']['approved'] != $calculatedTotal) {
                $this->addMessage('IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $reportedCounts['team1']['approved'], $calculatedTotal);
            }

            $calculatedTotal = $calculatedCounts['team2']['current']['registered'] + $calculatedCounts['team2']['future']['registered'];
            if ($reportedCounts['team2']['registered'] != $calculatedTotal) {
                $this->addMessage('IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', $reportedCounts['team2']['registered'], $calculatedTotal);
            }

            $calculatedTotal = $calculatedCounts['team2']['current']['approved'] + $calculatedCounts['team2']['future']['approved'];
            if ($reportedCounts['team2']['approved'] != $calculatedTotal) {
                $this->addMessage('IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', $reportedCounts['team2']['approved'], $calculatedTotal);
            }
        }

        return $this->isValid;
    }

    protected function calculateQuarterStartingCounts($tmlpRegistrations)
    {
        $output = [
            'team1' => [
                'current' => [
                    'registered' => 0,
                    'approved' => 0,
                ],
                'future' => [
                    'registered' => 0,
                    'approved' => 0,
                ],
            ],
            'team2' => [
                'current' => [
                    'registered' => 0,
                    'approved' => 0,
                ],
                'future' => [
                    'registered' => 0,
                    'approved' => 0,
                ],
            ],
        ];

        foreach ($tmlpRegistrations as $registration) {

            $team = $registration['incomingTeamYear'] == 1
                ? 'team1'
                : 'team2';

            $weekend = $registration['incomingWeekend'] == 'current'
                ? 'current'
                : 'future';

            $regDate = Util::parseUnknownDateFormat($registration['regDate']);

            if ($regDate && $regDate->lte($this->quarter->startWeekendDate)) {
                $output[$team][$weekend]['registered']++;
            }

            if ($registration['apprDate']) {
                $apprDate = Util::parseUnknownDateFormat($registration['apprDate']);

                if ($apprDate && $apprDate->lte($this->quarter->startWeekendDate)) {
                    $output[$team][$weekend]['approved']++;
                }
            }
        }

        return $output;
    }
}
