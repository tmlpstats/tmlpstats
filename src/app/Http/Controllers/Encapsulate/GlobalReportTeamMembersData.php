<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Encapsulations;
use TmlpStats\Reports\Arrangements;

class GlobalReportTeamMembersData
{
    private $globalReport;
    private $region;
    private $data = null;
    private $potentialsData = null;

    public function __construct(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $this->globalReport = $globalReport;
        $this->region = $region;
        $this->data = $this->getTeamMemberStatusData($globalReport, $region);
    }

    public function getTeamMemberStatusAllClassic()
    {
        $statusTypes = [
            'teammemberstatuswithdrawn',
            'teammemberstatusctw',
            'teammemberstatuswbo',
            'teammemberstatustransfer',
        ];

        $data = $this->data;
        $globalReport = $this->globalReport;
        $region = $this->region;

        $responseData = [];
        foreach ($statusTypes as $type) {
            $response = $this->getOne($type, $data);
            $responseData[$type] = $response ? $response->render() : '';
        }

        $potentialsData = $this->getTeamMemberStatusPotentialsData($data, $globalReport, $region);

        // The potentials reports use the same specialty data, so reuse it instead of processing twice
        $potentialTypes = [
            'potentialsdetails',
            'potentialsoverview',
        ];

        foreach ($potentialTypes as $type) {
            $response = $this->getOne($type, $potentialsData);
            $responseData[$type] = $response ? $response->render() : '';
        }

        return $responseData;
    }

    public function getOne($page)
    {
        $globalReport = $this->globalReport;
        $region = $this->region;
        $data = $this->data;
        if (!$data) {
            return null;
        }

        $viewData = null;
        switch ($page) {
            case 'TeamMemberStatusWithdrawn':
            case 'teammemberstatuswithdrawn':
                $viewData = $this->getTeamMemberStatusWithdrawn($data, $globalReport, $region);
                break;
            case 'TeamMemberStatusCtw':
            case 'teammemberstatusctw':
                $viewData = $this->getTeamMemberStatusCtw($data, $globalReport, $region);
                break;
            case 'TeamMemberStatusWbo':
            case 'teammemberstatuswbo':
                $viewData = $this->getTeamMemberStatusWbo($data, $globalReport, $region);
                break;
            case 'TeamMemberStatusTransfer':
            case 'teammemberstatustransfer':
                $viewData = $this->getTeamMemberStatusTransfer($data, $globalReport, $region);
                break;
            case 'Potentials':
            case 'potentialsdetails':
                $viewData = $this->getTeamMemberStatusPotentials($data, $globalReport, $region);
                break;
            case 'PotentialsOverview':
            case 'potentialsoverview':
                // Potentials Overview uses it's own view
                return $this->getTeamMemberStatusPotentialsOverview($globalReport, $region);
            default:
                throw new \Exception("Unknown page $page");
        }

        if ($viewData) {
            return view('globalreports.details.teammemberstatus', $viewData);
        }

        return null;
    }

    protected function getTeamMemberStatusData(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $teamMembers = App::make(Api\GlobalReport::class)->getClassListByCenter($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a = new Arrangements\TeamMembersByStatus(['teamMembersData' => $teamMembers]);

        return $a->compose();
    }

    protected function getTeamMemberStatusWithdrawn($data, Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $data ? array_merge($data, ['types' => ['withdrawn']]) : null;
    }

    protected function getTeamMemberStatusCtw($data, Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $data ? array_merge($data, ['types' => ['ctw']]) : null;
    }

    protected function getTeamMemberStatusWbo($data, Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $data ? array_merge($data, ['types' => ['wbo']]) : null;
    }

    protected function getTeamMemberStatusTransfer($data, Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $data ? array_merge($data, ['types' => ['xferIn', 'xferOut']]) : null;
    }

    protected function getTeamMemberStatusPotentials()
    {
        $data = $this->data;
        $potentialsData = $this->getTeamMemberStatusPotentialsData();

        return array_merge($data, $potentialsData, ['types' => ['t2Potential']]);
    }

    protected function getTeamMemberStatusPotentialsOverview(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $data = $this->data;
        // This caches so life is made easier
        $details = $this->getTeamMemberStatusPotentialsData();
        if (!$details) {
            return null;
        }

        $reportData = [];
        $totals = [
            'total' => 0,
            'registered' => 0,
            'approved' => 0,
        ];
        $quarters = [];

        foreach ($details['reportData']['t2Potential'] as $member) {
            $centerName = $member->center->name;

            if (!isset($reportData[$centerName])) {
                $reportData[$centerName] = [
                    'total' => 0,
                    'registered' => 0,
                    'approved' => 0,
                ];
            }
            $reportData[$centerName]['total']++;
            $totals['total']++;

            if ($appData = $details['registrations'][$member->teamMember->personId] ?? null) {
                $qid = $appData->incomingQuarterId;
                $rd = &$reportData[$centerName];
                $rd['registered']++;
                $totals['registered']++;
                $key = "registered{$qid}";
                $rd[$key] = ($rd[$key] ?? 0) + 1;
                $totals[$key] = ($totals[$key] ?? 0) + 1;
                if ($details['registrations'][$member->teamMember->personId]->apprDate) {
                    $rd['approved']++;
                    $totals['approved']++;
                    $key = "approved{$qid}";
                    $rd[$key] = ($rd[$key] ?? 0) + 1;
                    $totals[$key] = ($totals[$key] ?? 0) + 1;
                }
            }

            if (!isset($statsReports[$centerName])) {
                $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Models\Center::name($centerName)->first());
            }
        }
        $quarters = $details['quarters'];

        return view('globalreports.details.potentialsoverview', compact('reportData', 'totals', 'statsReports', 'quarters'));
    }

    protected function getTeamMemberStatusPotentialsData()
    {
        $potentialsData = $this->potentialsData;
        if ($potentialsData) {
            return $potentialsData;
        }

        $globalReport = $this->globalReport;
        $region = $this->region;
        $data = $this->data;
        if (!$data) {
            return null;
        }

        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        $quarters = collect([]);

        $potentialsThatRegistered = [];
        if ($registrations) {
            // Build list of T2 registrations, ignoring whitespace.
            $registrationsByCenterAndName = [];
            foreach ($registrations as $appdata) {
                $registration = $appdata->registration;
                if ($appdata->teamYear != 2 || $appdata->xferOut ||
                    $appdata->isWithdrawn() ||
                    $registration->isReviewer
                ) {
                    continue;
                }
                $registrationsByCenterAndName["{$appdata->center->id}/" .
                    trim($registration->person->firstName) . ' ' .
                    trim($registration->person->lastName)] = $appdata;
            }

            $potentials = $data['reportData']['t2Potential'];
            foreach ($potentials as $member) {
                $person = $member->teamMember->person;
                $key = "{$person->centerId}/" . trim($person->firstName) . ' ' . trim($person->lastName);

                if (isset($registrationsByCenterAndName[$key])) {
                    $potentialsThatRegistered[$member->teamMember->personId] = $appdata = $registrationsByCenterAndName[$key];
                    if (!$quarters->has($appdata->incomingQuarterId)) {
                        $quarters[$appdata->incomingQuarterId] = Encapsulations\RegionQuarter::ensure($region, $appdata->incomingQuarter);
                    }
                }
            }
        }

        return $this->potentialsData = array_merge($data, ['registrations' => $potentialsThatRegistered, 'quarters' => $quarters]);
    }

}
