<?php
namespace TmlpStats\Validate\Objects;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use Respect\Validation\Validator as v;

class CommCourseInfoValidator extends ObjectsValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_COURSES;

    protected function populateValidators($data)
    {
        $positiveIntValidator       = v::intVal()->min(0, true);
        $positiveIntOrNullValidator = v::optional($positiveIntValidator);

        $types = ['CAP', 'CPC'];

        $this->dataValidators['startDate']                  = v::date('Y-m-d');
        $this->dataValidators['location']                   = v::optional(v::stringType());
        $this->dataValidators['type']                       = v::in($types);
        $this->dataValidators['quarterStartTer']            = $positiveIntValidator;
        $this->dataValidators['quarterStartStandardStarts'] = $positiveIntValidator;
        $this->dataValidators['quarterStartXfer']           = $positiveIntValidator;
        $this->dataValidators['currentTer']                 = $positiveIntValidator;
        $this->dataValidators['currentStandardStarts']      = $positiveIntValidator;
        $this->dataValidators['currentXfer']                = $positiveIntValidator;
        $this->dataValidators['completedStandardStarts']    = $positiveIntOrNullValidator;
        $this->dataValidators['potentials']                 = $positiveIntOrNullValidator;
        $this->dataValidators['registrations']              = $positiveIntOrNullValidator;
        $this->dataValidators['guestsPromised']             = $positiveIntOrNullValidator;
        $this->dataValidators['guestsInvited']              = $positiveIntOrNullValidator;
        $this->dataValidators['guestsConfirmed']            = $positiveIntOrNullValidator;
        $this->dataValidators['guestsAttended']             = $positiveIntOrNullValidator;
    }

    protected function validate($data)
    {
        if (!$this->validateCourseBalance($data)) {
            $this->isValid = false;
        }
        if (!$this->validateCourseCompletionStats($data)) {
            $this->isValid = false;
        }
        if (!$this->validateCourseStartDate($data)) {
            $this->isValid = false;
        }
        if (!$this->validateGuestGame($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateCourseCompletionStats($data)
    {
        $isValid = true;

        $startDate = $this->getDateObject($data->startDate);
        if ($startDate && $startDate->lt($this->statsReport->reportingDate)) {
            if (is_null($data->completedStandardStarts)) {
                $this->addMessage('COMMCOURSE_COMPLETED_SS_MISSING');
                $isValid = false;
            }
            if (is_null($data->potentials)) {
                $this->addMessage('COMMCOURSE_POTENTIALS_MISSING');
                $isValid = false;
            }
            if (is_null($data->registrations)) {
                $this->addMessage('COMMCOURSE_REGISTRATIONS_MISSING');
                $isValid = false;
            }

            if (!is_null($data->completedStandardStarts) && !is_null($data->currentStandardStarts)) {
                if ($data->completedStandardStarts > $data->currentStandardStarts) {

                    $location = strtolower($data->location);

                    if ($this->statsReport->center->name == 'London' && ($location == 'germany' || $location == 'intl')) {
                        // Special case handling for courses in London where the standard starts count is different
                    } else {
                        $this->addMessage('COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS');
                        $isValid = false;
                    }
                } else if ($data->completedStandardStarts < ($data->currentStandardStarts - 3) && $startDate->diffInDays($this->statsReport->reportingDate) < 7) {

                    $withdrew = $data->currentStandardStarts - $data->completedStandardStarts;
                    $this->addMessage('COMMCOURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS', $withdrew);
                }
            }
        } else {
            if (!is_null($data->completedStandardStarts) || !is_null($data->potentials) || !is_null($data->registrations)) {
                $this->addMessage('COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE');
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateCourseStartDate($data)
    {
        $isValid = true;

        $startDate = $this->getDateObject($data->startDate);
        if ($startDate && $startDate->lt($this->statsReport->quarter->startWeekendDate)) {
            $this->addMessage('COMMCOURSE_COURSE_DATE_BEFORE_QUARTER');
            $isValid = false;
        }

        return $isValid;
    }

    public function validateCourseBalance($data)
    {
        $isValid = true;

        if (!is_null($data->quarterStartTer)
            && !is_null($data->quarterStartStandardStarts)
            && !is_null($data->quarterStartXfer)
        ) {
            if ($data->quarterStartTer < $data->quarterStartStandardStarts) {

                $this->addMessage('COMMCOURSE_QSTART_SS_GREATER_THAN_QSTART_TER', $data->quarterStartStandardStarts, $data->quarterStartTer);
                $isValid = false;
            }
            if ($data->quarterStartTer < $data->quarterStartXfer) {

                $this->addMessage('COMMCOURSE_QSTART_XFER_GREATER_THAN_QSTART_TER', $data->quarterStartXfer, $data->quarterStartTer);
                $isValid = false;
            }
        }
        if (!is_null($data->currentTer)
            && !is_null($data->currentStandardStarts)
            && !is_null($data->currentXfer)
        ) {
            if ($data->currentTer < $data->currentStandardStarts) {

                $this->addMessage('COMMCOURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER', $data->currentStandardStarts, $data->currentTer);
                $isValid = false;
            }
            if ($data->currentTer < $data->currentXfer) {

                $this->addMessage('COMMCOURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER', $data->currentXfer, $data->currentTer);
                $isValid = false;
            }

            if ($data->currentTer < (int)$data->quarterStartTer) {

                $this->addMessage('COMMCOURSE_CURRENT_TER_LESS_THAN_QSTART_TER', $data->currentTer, $data->quarterStartTer);
            }
            if ($data->currentXfer < (int)$data->quarterStartXfer) {

                $this->addMessage('COMMCOURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER', $data->currentXfer, $data->quarterStartXfer);
            }
        }

        return $isValid;
    }

    public function validateGuestGame($data)
    {
        $isValid = true;

        if (is_null($data->guestsPromised)) {
            return $isValid;
        }

        $startDate = $this->getDateObject($data->startDate);
        if ($startDate && $startDate->lt($this->statsReport->reportingDate)) {
            if (is_null($data->guestsInvited)) {
                $this->addMessage('COMMCOURSE_GUESTS_INVITED_MISSING');
                $isValid = false;
            }
            if (is_null($data->guestsConfirmed)) {
                $this->addMessage('COMMCOURSE_GUESTS_CONFIRMED_MISSING');
                $isValid = false;
            }
            if (is_null($data->guestsAttended)) {
                $this->addMessage('COMMCOURSE_GUESTS_ATTENDED_MISSING');
                $isValid = false;
            }
        } else {
            if (!is_null($data->guestsAttended)) {
                $this->addMessage('COMMCOURSE_GUESTS_ATTENDED_PROVIDED_BEFORE_COURSE');
                $isValid = false;
            }
        }

        return $isValid;
    }
}
