<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByStatus extends BaseArrangement
{
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [
            'xferIn'    => [],
            'xferOut'   => [],
            'ctw'       => [],
            'withdrawn' => [],
        ];
        foreach ($teamMembersData as $data) {
            if ($data->withdrawCodeId !== null) {
                $reportData['withdrawn'][] = $data;
            } else if ($data->ctw) {
                $reportData['ctw'][] = $data;
            } else if ($data->xferIn) {
                $reportData['xferIn'][] = $data;
            } else if ($data->xferOut) {
                $reportData['xferOut'][] = $data;
            }
        }

        return compact('reportData');
    }
}
