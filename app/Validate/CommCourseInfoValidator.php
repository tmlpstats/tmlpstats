<?php
namespace TmlpStats\Validate;

use Carbon\Carbon;
use Respect\Validation\Validator as v;

class CommCourseInfoValidator extends ValidatorAbstract
{
    protected $classDisplayName = 'CAP & CPC Course Info.';

    protected function populateValidators()
    {
        $positiveIntValidator        = v::int()->min(0, true);
        $positiveIntNotNullValidator = v::when(v::nullValue(), v::alwaysInvalid(), $positiveIntValidator);
        $positiveIntOrNullValidator  = v::when(v::nullValue(), v::alwaysValid(), $positiveIntValidator);
        $rowIdValidator              = v::numeric()->positive();

        $types = array('CAP', 'CPC');

        $this->dataValidators['startDate']                  = v::date('Y-m-d');
        $this->dataValidators['type']                       = v::in($types);
        // Skipping center (auto-generated)
        $this->dataValidators['statsReportId']              = $rowIdValidator;

        $this->dataValidators['reportingDate']              = v::date('Y-m-d');
        $this->dataValidators['courseId']                   = $rowIdValidator;
        $this->dataValidators['quarterStartTer']            = $positiveIntNotNullValidator;
        $this->dataValidators['quarterStartStandardStarts'] = $positiveIntNotNullValidator;
        $this->dataValidators['quarterStartXfer']           = $positiveIntNotNullValidator;
        $this->dataValidators['currentTer']                 = $positiveIntNotNullValidator;
        $this->dataValidators['currentStandardStarts']      = $positiveIntNotNullValidator;
        $this->dataValidators['currentXfer']                = $positiveIntNotNullValidator;
        $this->dataValidators['completedStandardStarts']    = $positiveIntOrNullValidator;
        $this->dataValidators['potentials']                 = $positiveIntOrNullValidator;
        $this->dataValidators['registrations']              = $positiveIntOrNullValidator;
        // Skipping quarter (auto-generated)
    }

    protected function validate()
    {
        $this->validateCourseBalance();
        $this->validateCourseCompletionStats();
        $this->validateCourseStartDate();

        return $this->isValid;
    }

    protected function validateCourseCompletionStats()
    {
        $statsReport = $this->getStatsReport($this->data->statsReportId);
        $startDate = $this->getDateObject($this->data->startDate);
        if ($startDate->lt($statsReport->reportingDate)) {
            if (is_null($this->data->completedStandardStarts)) {
                $this->addMessage('COMMCOURSE_COMPLETED_SS_MISSING');
                $this->isValid = false;
            }
            if (is_null($this->data->potentials)) {
                $this->addMessage('COMMCOURSE_POTENTIALS_MISSING');
                $this->isValid = false;
            }
            if (is_null($this->data->registrations)) {
                $this->addMessage('COMMCOURSE_REGISTRATIONS_MISSING');
                $this->isValid = false;
            }

            if (!is_null($this->data->completedStandardStarts) && !is_null($this->data->currentStandardStarts)) {
                if ($this->data->completedStandardStarts > $this->data->currentStandardStarts) {

                    $this->addMessage('COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS');
                    $this->isValid = false;
                } else if ($this->data->completedStandardStarts < ($this->data->currentStandardStarts - 3)) {

                    $withdrew = $this->data->currentStandardStarts - $this->data->completedStandardStarts;
                    $this->addMessage('COMMCOURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS', $withdrew);
                }
            }
        }
    }

    protected function validateCourseStartDate()
    {
        $statsReport = $this->getStatsReport($this->data->statsReportId);
        $startDate = $this->getDateObject($this->data->startDate);
        if ($startDate->lt($statsReport->quarter->startWeekendDate)) {
            $this->addMessage('COMMCOURSE_COURSE_DATE_BEFORE_QUARTER');
            $this->isValid = false;
        }
    }

    protected function validateCourseBalance()
    {
        if (!is_null($this->data->quarterStartTer)
            && !is_null($this->data->quarterStartStandardStarts)
            && !is_null($this->data->quarterStartXfer)
        ) {
            if ($this->data->quarterStartTer < $this->data->quarterStartStandardStarts) {

                $this->addMessage('COMMCOURSE_QSTART_SS_GREATER_THAN_QSTART_TER', $this->data->quarterStartStandardStarts, $this->data->quarterStartTer);
                $this->isValid = false;
            }
            if ($this->data->quarterStartTer < $this->data->quarterStartXfer) {

                $this->addMessage('COMMCOURSE_QSTART_XFER_GREATER_THAN_QSTART_TER', $this->data->quarterStartXfer, $this->data->quarterStartTer);
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->currentTer)
            && !is_null($this->data->currentStandardStarts)
            && !is_null($this->data->currentXfer)
        ) {
            if ($this->data->currentTer < $this->data->currentStandardStarts) {

                $this->addMessage('COMMCOURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER', $this->data->currentStandardStarts, $this->data->currentTer);
                $this->isValid = false;
            }
            if ($this->data->currentTer < $this->data->currentXfer) {

                $this->addMessage('COMMCOURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER', $this->data->currentXfer, $this->data->currentTer);
                $this->isValid = false;
            }
        }
    }
}
