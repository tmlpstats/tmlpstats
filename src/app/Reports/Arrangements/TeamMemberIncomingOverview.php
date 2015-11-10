<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\Quarter;

class TeamMemberIncomingOverview extends BaseArrangement
{
    /*
     * Builds an array of Tmlp Registrations by center with status counts
     */
    public function build($data)
    {
        $registrationsData = $data['registrationsData'];
        $teamMembersData = $data['teamMembersData'];
        $region = $data['region'];

        $reportData = [
            'team1' => [
                'applications' => [],
                'incoming'     => 0,
                'ongoing'      => 0,
            ],
            'team2' => [
                'applications' => [],
                'incoming'     => 0,
                'ongoing'      => 0,
            ],
        ];

        $thisQuarter = Quarter::current($region)->first();
        $nextQuarter = $thisQuarter->getNextQuarter();

        $a = new TmlpRegistrationsByStatus(compact('registrationsData'));
        $registrationStatusData = $a->compose();

        foreach ($registrationStatusData['reportData'] as $status => $statusData) {
            if ($status == 'total') {
                continue;
            }
            foreach ($statusData as $data) {
                $team = "team{$data->registration->teamYear}";
                if (!isset($reportData[$team]['applications'][$status])) {
                    $reportData[$team]['applications'][$status] = 0;
                }

                $reportData[$team]['applications'][$status]++;

                if ($status == 'approved' && $data->incomingQuarterId == $nextQuarter->id) {
                    $reportData[$team]['incoming']++;
                }
            }
        }

        $a = new TeamMembersByQuarter(compact('teamMembersData'));
        $membersData = $a->compose();

        foreach ($membersData['reportData'] as $team => $teamData) {
            if ($team == 'withdrawn') {
                continue;
            }
            foreach ($teamData as $quarter => $membersData) {
                if ($quarter == 'Q4') {
                    continue;
                }
                foreach ($membersData as $data) {
                    if ($data->xferOut) {
                        continue;
                    }
                    $reportData[$team]['ongoing']++;
                }
            }
        }

        return compact('reportData');
    }
}
