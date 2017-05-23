<?php namespace TmlpStats\Reports\Arrangements;

class TeamMembersByStatus extends BaseArrangement
{
    public function build($data)
    {
        $teamMembersData = $data['teamMembersData'];

        $reportData = [
            'xferIn'      => [],
            'xferOut'     => [],
            'wbo'         => [],
            'ctw'         => [],
            'withdrawn'   => [],
            't2Potential' => [],
        ];
        foreach ($teamMembersData as $data) {
            if ($data->withdrawCodeId !== null) {
                $reportData['withdrawn'][] = $data;
            } else if ($data->wbo) {
                $reportData['wbo'][] = $data;
            } else if ($data->ctw) {
                $reportData['ctw'][] = $data;
            } else if ($data->xferIn) {
                $reportData['xferIn'][] = $data;
            } else if ($data->xferOut) {
                $reportData['xferOut'][] = $data;
            }

            if ($data->teamYear == 1 && $data->quarterNumber == 4
                && $data->withdrawCodeId === null && !$data->ctw && !$data->xferOut && !$data->wbo
            ) {
                $reportData['t2Potential'][] = $data;
            }
        }

        return compact('reportData');
    }
}
