<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\Quarter;

class TravelRoomingByTeamYear extends BaseArrangement
{
    /*
     * Builds an array of Tmlp Registrations by center with status counts
     */
    public function build($data)
    {
        $registrationDataList = $data['registrationsData'];
        $teamMemberDataList = $data['teamMembersData'];
        $region = $data['region'];

        $reportData = [
            'incoming'    => [
                'team1' => [
                    'travel' => 0,
                    'room'   => 0,
                    'total'  => 0,
                ],
                'team2' => [
                    'travel' => 0,
                    'room'   => 0,
                    'total'  => 0,
                ],
            ],
            'teamMembers' => [
                'team1' => [
                    'travel' => 0,
                    'room'   => 0,
                    'total'  => 0,
                ],
                'team2' => [
                    'travel' => 0,
                    'room'   => 0,
                    'total'  => 0,
                ],
            ],
        ];

        $thisQuarter = Quarter::current($region)->first();
        $nextQuarter = $thisQuarter->getNextQuarter();

        foreach ($registrationDataList as $registrationData) {
            if ($registrationData->withdrawCodeId
                || $registrationData->xferOut
                || $registrationData->incomingQuarterId != $nextQuarter->id
            ) {
                continue;
            }

            $team = "team{$registrationData->registration->teamYear}";

            $reportData['incoming'][$team]['total']++;

            if ($registrationData->travel) {
                $reportData['incoming'][$team]['travel']++;
            }

            if ($registrationData->room) {
                $reportData['incoming'][$team]['room']++;
            }
        }

        foreach ($teamMemberDataList as $teamMemberData) {
            if (!$teamMemberData->isActiveMember()) {
                continue;
            }

            $team = "team{$teamMemberData->teamMember->teamYear}";

            $reportData['teamMembers'][$team]['total']++;

            if ($teamMemberData->travel) {
                $reportData['teamMembers'][$team]['travel']++;
            }

            if ($teamMemberData->room) {
                $reportData['teamMembers'][$team]['room']++;
            }
        }

        return compact('reportData');
    }
}
