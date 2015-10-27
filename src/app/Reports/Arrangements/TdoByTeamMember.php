<?php namespace TmlpStats\Reports\Arrangements;

use Carbon\Carbon;

class TdoByTeamMember extends BaseArrangement
{

    /* Builds an array of TDO attendance for each team member
     *
     */
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [];
        $dates = [];
        $members = [
            'team1' => [],
            'team2' => [],
        ];
        foreach ($teamMembersData as $date => $teamData) {
            $dates[] = Carbon::createFromFormat('Y-m-d', $date);
            foreach ($teamData as $data) {

                $teamYear = ($data->teamMember->teamYear == 1)
                    ? 'team1'
                    : 'team2';

                $member = isset($members[$teamYear][$data->teamMember->id])
                    ? $members[$teamYear][$data->teamMember->id]
                    : [];

                if (!$member) {
                    $member['member'] = $data->teamMember;
                    $member['withdrawn'] = false;
                }
                if ($data->withdrawCodeId !== null) {
                    $member['withdrawn'] = true;
                }
                $member[$date] = [
                    'attended' => $data->tdo,
                ];

                $members[$teamYear][$data->teamMember->id] = $member;
            }
        }
        $reportData['dates'] = $dates;
        $reportData['members'] = array_merge($members['team1'], $members['team2']);

        return compact('reportData');
    }
}
