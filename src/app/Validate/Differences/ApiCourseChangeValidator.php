<?php
namespace TmlpStats\Validate\Differences;

use TmlpStats\Validate\ApiValidatorAbstract;

class ApiCourseChangeValidator extends ApiValidatorAbstract
{
    protected function validate($data)
    {
        if (!$this->validateStartDateChange($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateStartDateChange($data)
    {
        $isValid = true;

        $lastWeek = count($this->pastWeeks) ? $this->pastWeeks[0] : null;
        if (!$lastWeek) {
            return true;
        }

        if ($data->startDate->ne($lastWeek->startDate)) {
            $this->addMessage('warning', [
                'id' => 'COURSE_START_DATE_CHANGED',
                'ref' => $data->getReference(['field' => 'startDate']),
                'params' => [
                    'now' => $data->startDate->format('M j, Y'),
                    'was' => $lastWeek->startDate->format('M j, Y'),
                ],
            ]);
        }

        return $isValid;
    }
}
