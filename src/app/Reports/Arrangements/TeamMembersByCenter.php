<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByCenter extends BaseArrangement
{
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [];

        foreach ($teamMembersData as $member) {
            $reportData[$member->center->name][] = $member;
        }

        return compact('reportData');
    }
}
