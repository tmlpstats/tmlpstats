<?php namespace TmlpStats\Reports\Arrangements;

use Carbon\Carbon;

abstract class TeamMemberWeeklyValue extends BaseArrangement
{
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

            // Weeks can be empty if we don't have any data from that week.
            if (!$teamData) {
                continue;
            }

            foreach ($teamData as $data) {

                $team = "team{$data->teamMember->teamYear}";

                $member = isset($members[$team][$data->teamMember->id])
                    ? $members[$team][$data->teamMember->id]
                    : [];

                if (!$member) {
                    $member['member'] = $data->teamMember;
                    $member['withdrawn'] = false;
                }
                if ($data->withdrawCodeId !== null) {
                    $member['withdrawn'] = true;
                }
                if (!$data->xferOut) {
                    $member[$date] = [
                        'value' => $this->getValue($data),
                    ];
                }

                $members[$team][$data->teamMember->id] = $member;
            }
        }
        $reportData['dates'] = $dates;
        $reportData['members'] = array_merge($members['team1'], $members['team2']);

        return compact('reportData');
    }

    abstract function getValue($data);
}
