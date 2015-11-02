<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByCenter extends BaseArrangement
{
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [];

        foreach ($teamMembersData as $data) {
            $reportData[$data->center->name][] = $data;
        }

        return compact('reportData');
    }
}
