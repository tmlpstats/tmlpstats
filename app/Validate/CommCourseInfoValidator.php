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
                $this->addMessage("Course has completed but is missing Standard Starts Completed", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->potentials)) {
                $this->addMessage("Course has completed but is missing Potentials", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->registrations)) {
                $this->addMessage("Course has completed but is missing Registrations", 'error');
                $this->isValid = false;
            }

            if (!is_null($this->data->completedStandardStarts) && !is_null($this->data->currentStandardStarts)) {
                if ($this->data->completedStandardStarts > $this->data->currentStandardStarts) {

                    $this->addMessage("More people completed the course than there were that started. Make sure Current Standard Starts matches the number of people that started the course, and Completed Standard Starts matches the number of people that completed the course.", 'error');
                    $this->isValid = false;
                } else if ($this->data->completedStandardStarts < ($this->data->currentStandardStarts - 3)) {

                    $withdrew = $this->data->currentStandardStarts - $this->data->completedStandardStarts;
                    $this->addMessage("Completed Standard Starts is $withdrew less than the course starting standard starts. Confirm that $withdrew people did withdraw during the course.", 'warning');
                }
            }
        }
    }

    protected function validateCourseStartDate()
    {
        $statsReport = $this->getStatsReport($this->data->statsReportId);
        $startDate = $this->getDateObject($this->data->startDate);
        if ($startDate->lt($statsReport->quarter->startWeekendDate)) {
            $this->addMessage("Course occured before quarter started", 'error');
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

                $this->addMessage("Quarter Starting Standard Starts ({$this->data->quarterStartStandardStarts}) cannot be more than the quarter starting total number of people ever registered in the course ({$this->data->quarterStartTer})", 'error');
                $this->isValid = false;
            }
            if ($this->data->quarterStartTer < $this->data->quarterStartXfer) {

                $this->addMessage("Quarter Starting Transfer ({$this->data->quarterStartXfer}) cannot be more than the quarter starting total number of people ever registered in the course ({$this->data->quarterStartTer})", 'error');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->currentTer)
            && !is_null($this->data->currentStandardStarts)
            && !is_null($this->data->currentXfer)
        ) {
            if ($this->data->currentTer < $this->data->currentStandardStarts) {

                $this->addMessage("Current Standard Starts ({$this->data->currentStandardStarts}) cannot be more than the total number of people ever registered in the course ({$this->data->currentTer})", 'error');
                $this->isValid = false;
            }
            if ($this->data->currentTer < $this->data->currentXfer) {

                $this->addMessage("Quarter Starting Transfer ({$this->data->currentXfer}) cannot be more than the total number of people ever registered in the course ({$this->data->currentTer})", 'error');
                $this->isValid = false;
            }
        }
    }
}
