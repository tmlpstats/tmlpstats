<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats\Models;
use TmlpStats\Traits;

// TODO: Review messages to make sure they are as accurate as possible

class ApiTeamApplicationValidator extends TmlpRegistrationValidator
{
    use Traits\GeneratesApiMessages, Traits\ValidatesTravelWithConfig;

    protected $startingNextQuarter = null;

    protected function populateValidators($data)
    {
        $idValidator         = v::numeric()->positive();
        $nameValidator       = v::stringType()->notEmpty();
        $dateValidator       = v::date('Y-m-d');
        $dateOrNullValidator = v::optional($dateValidator);
        $boolOrNullValidator = v::optional(v::boolType());

        $this->dataValidators['firstName']  = $nameValidator;
        $this->dataValidators['lastName']   = $nameValidator;
        $this->dataValidators['email']      = v::optional(v::email());
        $this->dataValidators['phone']      = v::optional(v::phone());
        $this->dataValidators['teamYear']   = v::numeric()->between(1, 2, true);
        $this->dataValidators['regDate']    = $dateValidator;
        $this->dataValidators['appOutDate'] = $dateOrNullValidator;
        $this->dataValidators['appInDate']  = $dateOrNullValidator;
        $this->dataValidators['apprDate']   = $dateOrNullValidator;
        $this->dataValidators['wdDate']     = $dateOrNullValidator;
        $this->dataValidators['travel']     = $boolOrNullValidator;
        $this->dataValidators['room']       = $boolOrNullValidator;
        $this->dataValidators['isReviewer'] = $boolOrNullValidator;
        $this->dataValidators['incomingQuarterId']     = $idValidator;
        $this->dataValidators['withdrawCodeId']        = v::optional($idValidator);
        $this->dataValidators['committedTeamMemberId'] = v::optional($idValidator);
    }

    protected function validate($data)
    {
        if (!$this->validateApprovalProcess($data)) {
            $this->isValid = false;
        }
        if (!$this->validateDates($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTravel($data)) {
            $this->isValid = false;
        }
        if (!$this->validateReviewer($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateApprovalProcess($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) || !is_null($data->wdDate)) {
            $col = 'wd';
            if (is_null($data->withdrawCodeId)) {
                $this->addMessage('TEAMAPP_WD_CODE_MISSING');
                $isValid = false;
            }
            if (is_null($data->wdDate)) {
                $this->addMessage('TEAMAPP_WD_DATE_MISSING');
                $isValid = false;
            }
        } else if (!is_null($data->apprDate)) {
            $col = 'appr';
            if (is_null($data->appInDate)) {
                $this->addMessage('TEAMAPP_APPR_MISSING_APPIN_DATE');
                $isValid = false;
            }
            if (is_null($data->appOutDate)) {
                $this->addMessage('TEAMAPP_APPR_MISSING_APPOUT_DATE');
                $isValid = false;
            }
        } else if (!is_null($data->appInDate)) {
            $col = 'in';
            if (is_null($data->appOutDate)) {
                $this->addMessage('TEAMAPP_APPIN_MISSING_APPOUT_DATE');
                $isValid = false;
            }
        }

        if (is_null($data->committedTeamMemberId) && is_null($data->withdrawCodeId)) {
            $this->addMessage('TMLPREG_NO_COMMITTED_TEAM_MEMBER');
            $isValid = false;
        }

        return $isValid;
    }

    public function validateDates($data)
    {
        $isValid = true;

        // Make sure dates for each step make sense
        if ($data->wdDate) {
            if ($data->regDate && $data->wdDate->lt($data->regDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
            if ($data->apprDate && $data->wdDate->lt($data->apprDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPR_DATE');
                $isValid = false;
            }
            if ($data->appInDate && $data->wdDate->lt($data->appInDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPIN_DATE');
                $isValid = false;
            }
            if ($data->appOutDate && $data->wdDate->lt($data->appOutDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPOUT_DATE');
                $isValid = false;
            }
        }
        if ($data->apprDate) {
            if ($data->regDate && $data->apprDate->lt($data->regDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
            if ($data->appInDate && $data->apprDate->lt($data->appInDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_APPIN_DATE');
                $isValid = false;
            }
            if ($data->appOutDate && $data->apprDate->lt($data->appOutDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE');
                $isValid = false;
            }
        }
        if ($data->appInDate) {
            if ($data->regDate && $data->appInDate->lt($data->regDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
            if ($data->appOutDate && $data->appInDate->lt($data->appOutDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE');
                $isValid = false;
            }
        }
        if ($data->appOutDate) {
            if ($data->regDate && $data->appOutDate->lt($data->regDate)) {
                $this->addMessage('TMLPREG_APPOUT_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
        }

        $maxAppOutDays      = static::MAX_DAYS_TO_SEND_APPLICATION_OUT;
        $maxApplicationDays = static::MAX_DAYS_TO_APPROVE_APPLICATION;
        $reportingDate = $this->statsReport->reportingDate;

        if (is_null($data->wdDate)) {
            // Make sure steps are taken in timely manner
            if (is_null($data->appOutDate)) {
                if ($data->regDate
                    && $data->regDate->lt($reportingDate)
                    && $data->regDate->diffInDays($reportingDate) > $maxAppOutDays
                ) {
                    $this->addMessage('TMLPREG_APPOUT_LATE', $maxAppOutDays);
                }
            } else if (is_null($data->appInDate)) {
                if ($data->appOutDate
                    && $data->appOutDate->lt($reportingDate)
                    && $data->appOutDate->diffInDays($reportingDate) > $maxApplicationDays
                ) {
                    $this->addMessage('TMLPREG_APPIN_LATE', $maxApplicationDays);
                }
            } else if (is_null($data->apprDate)) {
                if ($data->appInDate
                    && $data->appInDate->lt($reportingDate)
                    && $data->appInDate->diffInDays($reportingDate) > $maxApplicationDays
                ) {
                    $this->addMessage('TMLPREG_APPR_LATE', $maxApplicationDays);
                }
            }
        }

        // Make sure dates are in the past
        if (!is_null($data->regDate) && $reportingDate->lt($data->regDate)) {
            $this->addMessage('TMLPREG_REG_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->wdDate) && $reportingDate->lt($data->wdDate)) {
            $this->addMessage('TMLPREG_WD_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->apprDate) && $reportingDate->lt($data->apprDate)) {
            $this->addMessage('TMLPREG_APPR_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->appInDate) && $reportingDate->lt($data->appInDate)) {
            $this->addMessage('TMLPREG_APPIN_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->appOutDate) && $reportingDate->lt($data->appOutDate)) {
            $this->addMessage('TMLPREG_APPOUT_DATE_IN_FUTURE');
            $isValid = false;
        }

        return $isValid;
    }

    // TODO: Revisit this after we decide on the travel flow
    public function validateTravel($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) || !$this->isStartingNextQuarter($data)) {
            return $isValid; // Not required if withdrawn or future registration
        }

        // Travel and Rooming must be reported starting after the configured date
        if ($this->isTimeToCheckTravel()) {
            if (is_null($data->travel)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->addMessage('TEAMAPP_TRAVEL_COMMENT_MISSING');
                    $isValid = false;
                } else {
                    $this->addMessage('TEAMAPP_TRAVEL_COMMENT_REVIEW');
                }
            }
            if (is_null($data->room)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->addMessage('TEAMAPP_ROOM_COMMENT_MISSING');
                    $isValid = false;
                } else {
                    $this->addMessage('TEAMAPP_ROOM_COMMENT_REVIEW');
                }
            }
        }

        return $isValid;
    }

    public function validateReviewer($data)
    {
        $isValid = true;

        if ($data->isReviewer && $data->teamYear !== 2) {
            $this->addMessage('TEAMAPP_REVIEWER_TEAM1');
            $isValid = false;
        }

        return $isValid;
    }

    public function isStartingNextQuarter($data)
    {
        if ($this->startingNextQuarter !== null) {
            return $this->startingNextQuarter;
        }

        $nextQuarter = $this->statsReport->quarter->getNextQuarter();

        if (!$nextQuarter) {
            return false;
        }

        return $this->startingNextQuarter = ($nextQuarter->id === $data->incomingQuarterId);
    }
}
