<?php namespace TmlpStats\Reports\Arrangements;

class TmlpRegistrationsByIncomingQuarter extends BaseArrangement
{

    /* Builds an array of Tmlp Registration by incoming quarter
     *
     */
    public function build($data)
    {
        $registrationsData = $data['registrationsData'];
        $quarter = $data['quarter'];

        $reportData = [
            'team1'    => [],
            'team2'    => [],
            'withdrawn' => [],
        ];

        $nextQuarter = $quarter->getNextQuarter();

        foreach ($registrationsData as $data) {

            if ($data->withdrawCodeId !== null) {
                $reportData['withdrawn']['next'][] = $data;
            } else if ($data->registration->teamYear == 1) {
                if ($data->incomingQuarterId == $nextQuarter->id) {
                    $reportData['team1']['next'][] = $data;
                } else {
                    $reportData['team1']['future'][] = $data;
                }
            } else {
                if ($data->incomingQuarterId == $nextQuarter->id) {
                    $reportData['team2']['next'][] = $data;
                } else {
                    $reportData['team2']['future'][] = $data;
                }
            }
        }

        return compact('reportData');
    }
}
