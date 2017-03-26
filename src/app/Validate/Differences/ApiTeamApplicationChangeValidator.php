<?php
namespace TmlpStats\Validate\Differences;

use TmlpStats\Domain;
use TmlpStats\Validate\ApiValidatorAbstract;

class ApiTeamApplicationChangeValidator extends ApiValidatorAbstract
{
    protected function validate($data)
    {
        if (!$this->validateDateChanges($data)) {
            $this->isValid = false;
        }

        if (!$this->validateQuarterChanges($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateDateChanges($data)
    {
        $isValid = true;

        $lastWeek = count($this->pastWeeks) ? $this->pastWeeks[0] : null;
        if (!$lastWeek) {
            return true;
        }

        if ($data->regDate->ne($lastWeek->regDate)) {
            $this->addMessage('warning', [
                'id' => 'TEAMAPP_REG_DATE_CHANGED',
                'ref' => $data->getReference(['field' => 'regDate']),
                'params' => [
                    'now' => $data->regDate->format('M j, Y'),
                    'was' => $lastWeek->regDate->format('M j, Y'),
                ],
            ]);
        }

        if ($data->appOutDate && $lastWeek->appOutDate && $data->appOutDate->ne($lastWeek->appOutDate)) {
            $this->addMessage('warning', [
                'id' => 'TEAMAPP_APPOUT_DATE_CHANGED',
                'ref' => $data->getReference(['field' => 'appOutDate']),
                'params' => [
                    'now' => $data->appOutDate->format('M j, Y'),
                    'was' => $lastWeek->appOutDate->format('M j, Y'),
                ],
            ]);
        }

        if ($data->appInDate && $lastWeek->appInDate && $data->appInDate->ne($lastWeek->appInDate)) {
            $this->addMessage('warning', [
                'id' => 'TEAMAPP_APPIN_DATE_CHANGED',
                'ref' => $data->getReference(['field' => 'appInDate']),
                'params' => [
                    'now' => $data->appInDate->format('M j, Y'),
                    'was' => $lastWeek->appInDate->format('M j, Y'),
                ],
            ]);
        }

        if ($data->apprDate && $lastWeek->apprDate && $data->apprDate->ne($lastWeek->apprDate)) {
            $this->addMessage('warning', [
                'id' => 'TEAMAPP_APPR_DATE_CHANGED',
                'ref' => $data->getReference(['field' => 'apprDate']),
                'params' => [
                    'now' => $data->apprDate->format('M j, Y'),
                    'was' => $lastWeek->apprDate->format('M j, Y'),
                ],
            ]);
        }

        if ($data->wdDate && $lastWeek->wdDate && $data->wdDate->ne($lastWeek->wdDate)) {
            $this->addMessage('warning', [
                'id' => 'TEAMAPP_WD_DATE_CHANGED',
                'ref' => $data->getReference(['field' => 'wdDate']),
                'params' => [
                    'now' => $data->wdDate->format('M j, Y'),
                    'was' => $lastWeek->wdDate->format('M j, Y'),
                ],
            ]);
        }

        return $isValid;
    }

    public function validateQuarterChanges(Domain\TeamApplication $application)
    {
        if (!count($this->pastWeeks)) {
            return true;
        }
        $lastWeek = $this->pastWeeks[0];
        if ($application->incomingQuarterId != $lastWeek->incomingQuarterId) {
            // No need to worry about caching these CQ's because the context caches them
            $newCQ = Domain\CenterQuarter::ensure($application->center, $application->incomingQuarter);
            $wasCQ = Domain\CenterQuarter::ensure($application->center, $lastWeek->incomingQuarter);

            $this->addMessage('warning', [
                'id' => 'TEAMAPP_INCOMING_QUARTER_CHANGED',
                'ref' => $application->getReference(['field' => 'incomingQuarter']),
                'params' => [
                    'now' => $newCQ->displayString(),
                    'was' => $wasCQ->displayString(),
                ],
            ]);
        }

        return true; // this validation only includes warnings
    }
}
