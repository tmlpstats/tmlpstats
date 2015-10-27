<?php namespace TmlpStats\Reports\Arrangements;

class TmlpRegistrationsByStatus extends BaseArrangement
{

    /* Builds an array of Tmlp Registrations by status
     *
     */
    public function build($data)
    {
        $registrationsData = $data['registrationsData'];

        $reportData = [
            'notSent'   => [],
            'out'       => [],
            'waiting'   => [],
            'approved'  => [],
            'withdrawn' => [],
            'total'    => 0,
        ];

        foreach ($registrationsData as $data) {
            $reportData['total']++;

            if ($data->withdrawCodeId) {
                $reportData['withdrawn'][] = $data;
            } else if ($data->apprDate) {
                $reportData['approved'][] = $data;
            } else if ($data->appInDate) {
                $reportData['waiting'][] = $data;
            } else if ($data->appOutDate) {
                $reportData['out'][] = $data;
            } else {
                $reportData['notSent'][] = $data;
            }
        }

        return compact('reportData');
    }
}
