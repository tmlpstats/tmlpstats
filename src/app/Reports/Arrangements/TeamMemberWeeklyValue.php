<?php
namespace TmlpStats\Reports\Arrangements;

use Carbon\Carbon;

class TeamMemberWeeklyValue extends BaseArrangement
{
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];
        $field = $data['field'];

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

                $team = "team{$data->teamYear}";

                $member = isset($members[$team][$data->id])
                    ? $members[$team][$data->id]
                    : [];

                if (!$member) {
                    $member['member'] = $data;
                    $member['withdrawn'] = false;
                    $member['total'] = 0;
                }
                if ($data->withdrawCodeId !== null || $data->wbo) {
                    $member['withdrawn'] = true;
                } else {
                    // We need this to allow for team members that were withdrawn then
                    // rejoined team within the same quarter. Kind of a special case.
                    $member['withdrawn'] = false;
                }
                if (!$data->xferOut) {
                    $member[$date] = [
                        'value' => $data->$field,
                    ];
                    $member['total'] += $data->$field;
                }

                $members[$team][$data->id] = $member;
            }
        }
        $reportData['dates'] = $dates;
        $reportData['members'] = array_merge($members['team1'], $members['team2']);
        $reportData['type'] = $field;

        return compact('reportData');
    }
}
