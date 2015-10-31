<?php namespace TmlpStats\Reports\Arrangements;

class TmlpRegistrationsByOverdue extends BaseArrangement
{

    /*
     * Builds an array of Tmlp Registrations overdye by status
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

        foreach ($registrationsData as $status => $statusData) {
            if ($status == 'total') {
                continue;
            }
            foreach ($statusData as $data) {

                $due = $data->due();
                if (!$due || $data->statsReport->reportingDate->lt($due)) {
                    continue;
                }

                $reportData['total']++;
                $reportData[$status][] = $data;
            }
        }

        return compact('reportData');
    }
}
