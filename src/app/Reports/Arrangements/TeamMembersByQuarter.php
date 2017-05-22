<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByQuarter extends BaseArrangement
{
    /*
     * Builds an array of TDO attendance for each team member
     */
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];
        $includeXferAsWithdrawn = array_get($data, 'includeXferAsWithdrawn', false);

        $reportData = [
            'team1'     => [],
            'team2'     => [],
            'withdrawn' => [],
        ];

        foreach ($teamMembersData as $memberData) {

            $index = "team{$memberData->teamMember->teamYear}";
            if ($memberData->withdrawCodeId !== null
                || ($includeXferAsWithdrawn && $memberData->xferOut)
                || $memberData->wbo
            ) {
                $index = 'withdrawn';
            }

            $reportData[$index]["Q{$memberData->teamMember->quarterNumber}"][] = $memberData;
        }

        return compact('reportData');
    }
}
