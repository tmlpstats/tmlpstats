<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Reports\Arrangements;

class GlobalReportTeamSummaryData
{
    private $globalReport;
    private $region;
    private $data = null;
    private $potentialsData = null;

    public function __construct(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $this->globalReport = $globalReport;
        $this->region = $region;
    }

    public function getTeamSummaryGrid(Models\GlobalReport $globalReport, Models\Region $region, $teamYear)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$registrations) {
            return null;
        }

        $teamMembers = App::make(Api\GlobalReport::class)->getClassListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$teamMembers) {
            return null;
        }

        $thisQuarter = Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region);
        $nextQuarter = $thisQuarter->getNextQuarter();

        $thisQuarterStartDate = $thisQuarter->getQuarterStartDate();

        $centerGamesData = App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter($globalReport, $region, [
            'date' => $thisQuarter->getQuarterEndDate(),
        ]);
        if (!$centerGamesData) {
            return null;
        }

        $template = [
            'qtrPromise' => 0,
            'registrations' => [
                'total' => 0,
                'net' => 0,
            ],
            'wkndReg' => [
                'before' => 0,
                'during' => 0,
                'after' => 0,
            ],
            'appStatus' => [
                'appOut' => 0,
                'appIn' => 0,
                'appr' => 0,
                'wd' => 0,
            ],
            'appStatusNext' => [
                'appOut' => 0,
                'appIn' => 0,
                'appr' => 0,
                'wd' => 0,
            ],
            'onTeamAtWknd' => 0,
            'xferIn' => 0,
            'xferOut' => 0,
            'withdraws' => [
                'q1' => 0,
                'q2' => 0,
                'q3' => 0,
                'q4' => 0,
                'all' => 0,
            ],
            'wbo' => 0,
            'ctw' => 0,
            'rereg' => 0,
            'currentOnTeam' => 0,
            'tdo' => 0,
            'completing' => 0,
            'onTeamNextQtr' => 0,
            'attendingWeekend' => 0,
        ];

        $reportData = [];
        foreach ($centerGamesData as $centerName => $centerData) {
            $reportData[$centerName] = $template;
            $reportData[$centerName]['qtrPromise'] = $centerGamesData[$centerName]['promise']["t{$teamYear}x"];
        }

        foreach ($registrations as $registration) {
            $centerName = $registration->statsReport->center->name;

            if ($registration->teamYear != $teamYear) {
                continue;
            }

            if ($registration->regDate->lte($thisQuarterStartDate)) {
                $reportData[$centerName]['wkndReg']['before']++;
            } else if ($teamYear == 2 && $registration->regDate->lte($thisQuarterStartDate->copy()->addDays(2))) {
                $reportData[$centerName]['wkndReg']['during']++;
            } else {
                $reportData[$centerName]['wkndReg']['after']++;
            }

            $reportData[$centerName]['registrations']['total']++;
            if ($registration->withdrawCode !== null) {
                $reportData[$centerName]['appStatus']['wd']++;
                if ($registration->incomingQuarterId === $nextQuarter->id) {
                    $reportData[$centerName]['appStatusNext']['wd']++;
                }
            } else if ($registration->apprDate !== null) {
                $reportData[$centerName]['registrations']['net']++;
                $reportData[$centerName]['appStatus']['appr']++;
                if ($registration->incomingQuarterId === $nextQuarter->id) {
                    $reportData[$centerName]['appStatusNext']['appr']++;
                    $reportData[$centerName]['onTeamNextQtr']++;
                    $reportData[$centerName]['attendingWeekend']++;
                }
            } else if ($registration->appInDate !== null) {
                $reportData[$centerName]['appStatus']['appIn']++;
                if ($registration->incomingQuarterId === $nextQuarter->id) {
                    $reportData[$centerName]['appStatusNext']['appIn']++;
                }
            } else if ($registration->appOutDate !== null) {
                $reportData[$centerName]['appStatus']['appOut']++;
                if ($registration->incomingQuarterId === $nextQuarter->id) {
                    $reportData[$centerName]['appStatusNext']['appOut']++;
                }
            }
        }

        foreach ($teamMembers as $member) {
            $centerName = $member->statsReport->center->name;

            if ($member->teamYear != $teamYear) {
                continue;
            }

            if (!isset($reportData[$centerName])) {
                $reportData[$centerName] = $template;
            }

            if ($member->xferIn) {
                $reportData[$centerName]['xferIn']++;
            } else if ($member->atWeekend) {
                $reportData[$centerName]['onTeamAtWknd']++;
            }

            if ($member->xferOut) {
                $reportData[$centerName]['xferOut']++;
            }

             if ($member->withdrawCode !== null) {
                $reportData[$centerName]['withdraws']['q' . $member->quarterNumber]++;
                $reportData[$centerName]['withdraws']['all']++;
            }

            if ($member->wbo) {
                $reportData[$centerName]['wbo']++;
            }

            if ($member->ctw) {
                $reportData[$centerName]['ctw']++;
            }

            if ($member->rereg) {
                $reportData[$centerName]['rereg']++;
            }

            if ($member->isActiveMember()) {
                $reportData[$centerName]['currentOnTeam']++;
                $reportData[$centerName]['attendingWeekend']++;

                if ($member->quarterNumber == 4) {
                    $reportData[$centerName]['completing']++;
                } else {
                    $reportData[$centerName]['onTeamNextQtr']++;
                }
            }

            if ($member->tdo) {
                $reportData[$centerName]['tdo']++;
            }
        }
        ksort($reportData);

        $totals = $template;
        foreach ($reportData as $centerName => $centerData) {
            foreach ($centerData as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $totals[$key][$subKey] += $subValue;
                    }
                } else {
                    $totals[$key] += $value;
                }
            }
        }

        $regFulfill = $template;

        if ($totals['registrations']['total']) {
            $regFulfill['appStatus']['appOut'] = $totals['appStatus']['appOut'] / $totals['registrations']['total'];
            $regFulfill['appStatus']['appIn'] = $totals['appStatus']['appIn'] / $totals['registrations']['total'];
            $regFulfill['appStatus']['appr'] = $totals['appStatus']['appr'] / $totals['registrations']['total'];
            $regFulfill['appStatus']['wd'] = $totals['appStatus']['wd'] / $totals['registrations']['total'];
        }

        $appStatusNextSum = $totals['appStatusNext']['appOut']
            + $totals['appStatusNext']['appIn']
            + $totals['appStatusNext']['appr']
            + $totals['appStatusNext']['wd'];

        if ($appStatusNextSum) {
            $regFulfill['appStatusNext']['appOut'] = $totals['appStatusNext']['appOut'] / $appStatusNextSum;
            $regFulfill['appStatusNext']['appIn'] = $totals['appStatusNext']['appIn'] / $appStatusNextSum;
            $regFulfill['appStatusNext']['appr'] = $totals['appStatusNext']['appr'] / $appStatusNextSum;
            $regFulfill['appStatusNext']['wd'] = $totals['appStatusNext']['wd'] / $appStatusNextSum;
        }

        if ($totals['onTeamAtWknd']) {
            $regFulfill['withdraws']['all'] = $totals['withdraws']['all'] / $totals['onTeamAtWknd'];
        }

        foreach ($regFulfill as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $regFulfill[$key][$subKey] = round($subValue * 100);
                }
            } else {
                $regFulfill[$key] = round($value * 100);
            }
        }

        return compact('reportData', 'totals', 'regFulfill', 'teamYear');
    }

    public function getOne($page)
    {
        $globalReport = $this->globalReport;
        $region = $this->region;

        $viewData = null;
        switch (strtolower($page)) {
            case 'team1summarygrid':
                $viewData = $this->getTeamSummaryGrid($globalReport, $region, 1);
                break;
            case 'team2summarygrid':
                $viewData = $this->getTeamSummaryGrid($globalReport, $region, 2);
                break;
            default:
                throw new \Exception("Unknown page $page");
        }

        if ($viewData) {
            return view('globalreports.details.teamsummarygrid', $viewData);
        }

        return null;
    }
}
