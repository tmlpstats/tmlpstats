<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByQuarter extends BaseArrangement
{
    /*
     * Builds an array of TDO attendance for each team member
     */
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [
            'team1'     => [],
            'team2'     => [],
            'withdrawn' => [],
        ];

        foreach ($teamMembersData as $data) {

            $index = $data->withdrawCodeId !== null
                ? 'withdrawn'
                : "team{$data->teamMember->teamYear}";

            $reportData[$index]["Q{$data->teamMember->quarterNumber}"][] = $data;
        }

        return compact('reportData');
    }
}
