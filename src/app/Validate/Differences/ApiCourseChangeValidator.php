<?php
namespace TmlpStats\Validate\Differences;

use TmlpStats\Encapsulations;
use TmlpStats\Validate\ApiValidatorAbstract;

class ApiCourseChangeValidator extends ApiValidatorAbstract
{
    protected function validate($data)
    {
        if (!$this->validateStartDateChange($data)) {
            $this->isValid = false;
        }
        if (!$this->validateQuarterStartChanges($data)) {
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

    public function validateQuarterStartChanges($data)
    {
        $isValid = true;

        $lastWeek = count($this->pastWeeks) ? $this->pastWeeks[0] : null;
        if (!$lastWeek) {
            return true;
        }

        if ($this->isFirstWeek()) {
            $this->checkQstartFirstWeek($data, $lastWeek);
        } else {
            $this->checkQstartLaterWeeks($data, $lastWeek);
        }

        return $isValid;
    }

    /**
     * During the first week, we check the quarter starting values against the previous week's current values
     *
     * @param  array $data
     * @param  array $lastWeek
     */
    public function checkQstartFirstWeek($data, $lastWeek)
    {
        if ($data->quarterStartTer != $lastWeek->currentTer) {
            $this->addMessage('warning', [
                'id' => 'COURSE_QSTART_TER_DOES_NOT_MATCH_QEND',
                'ref' => $data->getReference(['field' => 'quarterStartTer']),
                'params' => [
                    'now' => $data->quarterStartTer,
                    'was' => $lastWeek->currentTer,
                ],
            ]);
        }
        if ($data->quarterStartStandardStarts != $lastWeek->currentStandardStarts) {
            $this->addMessage('warning', [
                'id' => 'COURSE_QSTART_SS_DOES_NOT_MATCH_QEND',
                'ref' => $data->getReference(['field' => 'quarterStartStandardStarts']),
                'params' => [
                    'now' => $data->quarterStartStandardStarts,
                    'was' => $lastWeek->currentStandardStarts,
                ],
            ]);
        }
        if ($data->quarterStartXfer != $lastWeek->currentXfer) {
            $this->addMessage('warning', [
                'id' => 'COURSE_QSTART_XFER_DOES_NOT_MATCH_QEND',
                'ref' => $data->getReference(['field' => 'quarterStartXfer']),
                'params' => [
                    'now' => $data->quarterStartXfer,
                    'was' => $lastWeek->currentXfer,
                ],
            ]);
        }
    }

    /**
     * During the subsequent weeks, we check the quarter starting values against the previous week's quarter starting values
     *
     * @param  array $data
     * @param  array $lastWeek
     */
    public function checkQstartLaterWeeks($data, $lastWeek)
    {
        if ($data->quarterStartTer != $lastWeek->quarterStartTer) {
            $this->addMessage('warning', [
                'id' => 'COURSE_QSTART_TER_CHANGED',
                'ref' => $data->getReference(['field' => 'quarterStartTer']),
                'params' => [
                    'now' => $data->quarterStartTer,
                    'was' => $lastWeek->quarterStartTer,
                ],
            ]);
        }
        if ($data->quarterStartStandardStarts != $lastWeek->quarterStartStandardStarts) {
            $this->addMessage('warning', [
                'id' => 'COURSE_QSTART_SS_CHANGED',
                'ref' => $data->getReference(['field' => 'quarterStartStandardStarts']),
                'params' => [
                    'now' => $data->quarterStartStandardStarts,
                    'was' => $lastWeek->quarterStartStandardStarts,
                ],
            ]);
        }
        if ($data->quarterStartXfer != $lastWeek->quarterStartXfer) {
            $this->addMessage('warning', [
                'id' => 'COURSE_QSTART_XFER_CHANGED',
                'ref' => $data->getReference(['field' => 'quarterStartXfer']),
                'params' => [
                    'now' => $data->quarterStartXfer,
                    'was' => $lastWeek->quarterStartXfer,
                ],
            ]);
        }
    }

    public function isFirstWeek()
    {
        $cq = Encapsulations\CenterReportingDate::ensure($this->center, $this->reportingDate)->getCenterQuarter();
        return $this->reportingDate->eq($cq->firstWeekDate);
    }
}
