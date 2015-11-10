<?php namespace TmlpStats\Reports\Arrangements;

class TmlpRegistrationsByCenter extends BaseArrangement
{
    /*
     * Builds an array of Tmlp Registrations by center
     */
    public function build($data)
    {
        $registrationsData = $data['registrationsData'];

        $reportData = [];

        foreach ($registrationsData as $data) {
            $reportData[$data->center->name][] = $data;
        }

        return compact('reportData');
    }
}
