<?php namespace TmlpStats\Reports\Arrangements;

class TmlpRegistrationsByIncomingQuarter extends BaseArrangement
{
    /*
     * Builds an array of Tmlp Registrations by incoming quarter
     */
    public function build($data)
    {
        $registrationsData = $data['registrationsData'];
        $quarter = $data['quarter'];

        $reportData = [
            'team1'     => [],
            'team2'     => [],
            'withdrawn' => [],
        ];

        $nextQuarter = $quarter->getNextQuarter();

        foreach ($registrationsData as $data) {

            $index = "team{$data->registration->teamYear}";
            if ($data->withdrawCodeId !== null) {
                $index = 'withdrawn';
            }

            if ($data->incomingQuarterId == $nextQuarter->id) {
                $reportData[$index]['next'][] = $data;
            } else {
                $reportData[$index]['future'][] = $data;
            }
        }

        return compact('reportData');
    }
}
