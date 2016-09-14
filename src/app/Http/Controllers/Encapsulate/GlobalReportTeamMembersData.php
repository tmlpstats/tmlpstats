<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
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

            if (isset($details['registrations'][$member->teamMember->personId])) {
                $reportData[$centerName]['registered']++;
                $totals['registered']++;
                if ($details['registrations'][$member->teamMember->personId]->apprDate) {
                    $reportData[$centerName]['approved']++;
                    $totals['approved']++;
                }
            }

            if (!isset($statsReports[$centerName])) {
                $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Models\Center::name($centerName)->first());
            }
        }

        return view('globalreports.details.potentialsoverview', compact('reportData', 'totals', 'statsReports'));
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

        $potentialsThatRegistered = [];
        if ($registrations) {

            $potentials = $data['reportData']['t2Potential'];
            foreach ($potentials as $member) {
                foreach ($registrations as $registration) {
                    if ($registration->teamYear == 2
                        && !$registration->isWithdrawn()
                        && $registration->center->id == $member->center->id
                    ) {
                        if ($member->teamMember->personId == $registration->registration->personId) {
                            $potentialsThatRegistered[$member->teamMember->personId] = $registration;
                            break;
                        }
                    }
                }
            }
        }

        $potentialsData = $this->potentialsData = array_merge($data, ['registrations' => $potentialsThatRegistered]);

        return $potentialsData;
    }

}
