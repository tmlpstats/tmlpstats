<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByQuarter extends BaseArrangement
{

    /* Builds an array of TDO attendance for each team member
     *
     */
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [];
        foreach ($teamMembersData as $data) {
            if ($data->withdrawCodeId !== null) {
                $reportData['withdrawn']["Q{$data->teamMember->quarterNumber}"][] = $data;
            } else if ($data->teamMember->teamYear == 1) {
                $reportData['team1']["Q{$data->teamMember->quarterNumber}"][] = $data;
            } else {
                $reportData['team2']["Q{$data->teamMember->quarterNumber}"][] = $data;
            }
        }

        return compact('reportData');
    }
}
