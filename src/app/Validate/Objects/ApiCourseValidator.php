<?php
namespace TmlpStats\Validate\Objects;

use Carbon\Carbon;
use Respect\Validation\Validator as v;
use TmlpStats\Traits;

class ApiCourseValidator extends ApiObjectsValidatorAbstract
{
    use Traits\ValidatesTravelWithConfig;

    protected function populateValidators($data)
    {
        $positiveIntValidator = v::intVal()->min(0, true);
        $positiveIntOrNullValidator = v::optional($positiveIntValidator);

        $types = ['CAP', 'CPC'];

        $this->dataValidators['startDate'] = v::date('Y-m-d');
        $this->dataValidators['location'] = v::optional(v::stringType());
        $this->dataValidators['type'] = v::in($types);
        $this->dataValidators['quarterStartTer'] = $positiveIntValidator;
        $this->dataValidators['quarterStartStandardStarts'] = $positiveIntValidator;
        $this->dataValidators['quarterStartXfer'] = $positiveIntValidator;
        $this->dataValidators['currentTer'] = $positiveIntValidator;
        $this->dataValidators['currentStandardStarts'] = $positiveIntValidator;
        $this->dataValidators['currentXfer'] = $positiveIntValidator;
        $this->dataValidators['completedStandardStarts'] = $positiveIntOrNullValidator;
        $this->dataValidators['potentials'] = $positiveIntOrNullValidator;
        $this->dataValidators['registrations'] = $positiveIntOrNullValidator;
        $this->dataValidators['guestsPromised'] = $positiveIntOrNullValidator;
        $this->dataValidators['guestsInvited'] = $positiveIntOrNullValidator;
        $this->dataValidators['guestsConfirmed'] = $positiveIntOrNullValidator;
        $this->dataValidators['guestsAttended'] = $positiveIntOrNullValidator;
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
                $this->addMessage('error', [
                    'id' => 'COURSE_COMPLETED_SS_MISSING',
                    'ref' => $data->getReference(['field' => 'completedStandardStarts']),
                ]);
                $isValid = false;
            }
            if (is_null($data->potentials)) {
                $this->addMessage('error', [
                    'id' => 'COURSE_POTENTIALS_MISSING',
                    'ref' => $data->getReference(['field' => 'potentials']),
                ]);
                $isValid = false;
            }
            if (is_null($data->registrations)) {
                $this->addMessage('error', [
                    'id' => 'COURSE_REGISTRATIONS_MISSING',
                    'ref' => $data->getReference(['field' => 'registrations']),
                ]);
                $isValid = false;
            }

            if (!is_null($data->completedStandardStarts) && !is_null($data->currentStandardStarts)) {
                if ($data->completedStandardStarts > $data->currentStandardStarts) {

                    $location = strtolower($data->location);

                    if ($this->statsReport->center->name == 'London' && ($location == 'germany' || $location == 'intl')) {
                        // Special case handling for courses in London where the standard starts count is different
                    } else {
                        $this->addMessage('error', [
                            'id' => 'COURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS',
                            'ref' => $data->getReference(['field' => 'completedStandardStarts']),
                        ]);
                        $isValid = false;
                    }
                } else if ($data->completedStandardStarts < ($data->currentStandardStarts - 3) && $startDate->diffInDays($this->statsReport->reportingDate) < 7) {

                    $withdrew = $data->currentStandardStarts - $data->completedStandardStarts;
                    $this->addMessage('warning', [
                        'id' => 'COURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS',
                        'ref' => $data->getReference(['field' => 'completedStandardStarts']),
                        'params' => ['delta' => $withdrew],
                    ]);
                }
            }

            if (!is_null($data->potentials) && !is_null($data->registrations)) {
                if ($data->potentials < $data->registrations) {
                    $this->addMessage('error', [
                        'id' => 'COURSE_COMPLETED_REGISTRATIONS_GREATER_THAN_POTENTIALS',
                        'ref' => $data->getReference(['field' => 'registrations']),
                        'params' => [
                            'registrations' => $data->potentials,
                            'potentials' => $data->registrations,
                        ],
                    ]);
                    $isValid = false;
                }
            }
        } else {
            if (!is_null($data->completedStandardStarts) || !is_null($data->potentials) || !is_null($data->registrations)) {
                $field = '';
                if(!is_null($data->completedStandardStarts)) {
                    $field = 'completedStandardStarts';
                }
                if(!is_null($data->potentials)) {
                    $field = 'potentials';
                }
                if (!is_null($data->registrations)) {
                    $field = 'registrations';
                }
                $this->addMessage('error', [
                    'id' => 'COURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE',
                    'ref' => $data->getReference(['field' => $field]),
                ]);
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateCourseStartDate($data)
    {
        $isValid = true;

        $startDate = $this->getDateObject($data->startDate);
        if ($startDate && $startDate->lt($this->statsReport->quarter->getQuarterStartDate($this->statsReport->center))) {
            $this->addMessage('error', [
                'id' => 'COURSE_COURSE_DATE_BEFORE_QUARTER',
                'ref' => $data->getReference(['field' => 'startDate']),
            ]);
            $isValid = false;
        }

        if (!$this->pastWeeks && $startDate && $startDate->dayOfWeek !== Carbon::SATURDAY) {
            $this->addMessage('warning', [
                'id' => 'COURSE_START_DATE_NOT_SATURDAY',
                'ref' => $data->getReference(['field' => 'startDate']),
            ]);
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
                $this->addMessage('error', [
                    'id' => 'COURSE_QSTART_SS_GREATER_THAN_QSTART_TER',
                    'ref' => $data->getReference(['field' => 'quarterStartStandardStarts']),
                    'params' => [
                        'starts' => $data->quarterStartStandardStarts,
                        'ter' => $data->quarterStartTer,
                    ],
                ]);
                $isValid = false;
            }
            if ($data->quarterStartTer < $data->quarterStartXfer) {
                $this->addMessage('error', [
                    'id' => 'COURSE_QSTART_XFER_GREATER_THAN_QSTART_TER',
                    'ref' => $data->getReference(['field' => 'quarterStartXfer']),
                    'params' => [
                        'xfer' => $data->quarterStartXfer,
                        'ter' => $data->quarterStartTer,
                    ],
                ]);
                $isValid = false;
            }
        }
        if (!is_null($data->currentTer)
            && !is_null($data->currentStandardStarts)
            && !is_null($data->currentXfer)
        ) {
            if ($data->currentTer < $data->currentStandardStarts) {
                $this->addMessage('error', [
                    'id' => 'COURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER',
                    'ref' => $data->getReference(['field' => 'currentStandardStarts']),
                    'params' => [
                        'starts' => $data->currentStandardStarts,
                        'ter' => $data->currentTer,
                    ],
                ]);
                $isValid = false;
            }
            if ($data->currentTer < $data->currentXfer) {
                $this->addMessage('error', [
                    'id' => 'COURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER',
                    'ref' => $data->getReference(['field' => 'currentXfer']),
                    'params' => [
                        'xfer' => $data->currentXfer,
                        'ter' => $data->currentTer,
                    ],
                ]);
                $isValid = false;
            }

            $lastWeek = count($this->pastWeeks) ? $this->pastWeeks[0] : null;

            if ($data->currentTer < $data->quarterStartTer
                && (!$lastWeek || $lastWeek->currentTer >= $lastWeek->quarterStartTer)
            ) {
                $this->addMessage('warning', [
                    'id' => 'COURSE_CURRENT_TER_LESS_THAN_QSTART_TER',
                    'ref' => $data->getReference(['field' => 'currentTer']),
                    'params' => [
                        'currentTer' => $data->currentTer,
                        'quarterStartTer' => $data->quarterStartTer,
                    ],
                ]);
            }
            if ($data->currentXfer < $data->quarterStartXfer
                && (!$lastWeek || $lastWeek->currentTer >= $lastWeek->quarterStartXfer)
            ) {
                $this->addMessage('warning', [
                    'id' => 'COURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER',
                    'ref' => $data->getReference(['field' => 'currentXfer']),
                    'params' => [
                        'currentXfer' => $data->currentXfer,
                        'quarterStartXfer' => $data->quarterStartXfer,
                    ],
                ]);
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
                $this->addMessage('error', [
                    'id' => 'COURSE_GUESTS_INVITED_MISSING',
                    'ref' => $data->getReference(['field' => 'guestsInvited']),
                ]);
                $isValid = false;
            }
            if (is_null($data->guestsConfirmed)) {
                $this->addMessage('error', [
                    'id' => 'COURSE_GUESTS_CONFIRMED_MISSING',
                    'ref' => $data->getReference(['field' => 'guestsConfirmed']),
                ]);
                $isValid = false;
            }
            if (is_null($data->guestsAttended)) {
                $this->addMessage('error', [
                    'id' => 'COURSE_GUESTS_ATTENDED_MISSING',
                    'ref' => $data->getReference(['field' => 'guestsAttended']),
                ]);
                $isValid = false;
            }
        } else {
            if (!is_null($data->guestsAttended)) {
                $this->addMessage('error', [
                    'id' => 'COURSE_GUESTS_ATTENDED_PROVIDED_BEFORE_COURSE',
                    'ref' => $data->getReference(['field' => 'guestsAttended']),
                ]);
                $isValid = false;
            }
        }

        return $isValid;
    }
}
