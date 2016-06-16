<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats\Traits;

class ApiTeamMemberValidator extends ClassListValidator
{
    use Traits\GeneratesApiMessages, Traits\ValidatesTravelWithConfig;

    protected function populateValidators($data)
    {
        $idValidator         = v::numeric()->positive();
        $nameValidator       = v::stringType()->notEmpty();
        $boolValidator       = v::boolType();
        $boolOrNullValidator = v::optional($boolValidator);
        $teamYearValidator   = v::numeric()->between(1, 2, true);

        $this->dataValidators['firstName']  = $nameValidator;
        $this->dataValidators['lastName']   = $nameValidator;
        $this->dataValidators['teamYear']   = $teamYearValidator;
        $this->dataValidators['atWeekend']  = $boolValidator;
        $this->dataValidators['isReviewer'] = $boolOrNullValidator;
        $this->dataValidators['xferOut']    = $boolOrNullValidator;
        $this->dataValidators['xferIn']     = $boolOrNullValidator;
        $this->dataValidators['ctw']        = $boolOrNullValidator;
        $this->dataValidators['rereg']      = $boolOrNullValidator;
        $this->dataValidators['excep']      = $boolOrNullValidator;
        $this->dataValidators['travel']     = $boolOrNullValidator;
        $this->dataValidators['room']       = $boolOrNullValidator;
        $this->dataValidators['gitw']       = $boolOrNullValidator;
        $this->dataValidators['tdo']        = $boolOrNullValidator;
        $this->dataValidators['withdrawCodeId'] = v::optional($idValidator);
    }

    protected function validate($data)
    {
        if (!$this->validateGitw($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTdo($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTeamYear($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTransfer($data)) {
            $this->isValid = false;
        }
        if (!$this->validateWithdraw($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTravel($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateGitw($data)
    {
        $isValid = true;

        if (!is_null($data->xferOut) || !is_null($data->withdrawCodeId)) {
            if (!is_null($data->gitw)) {
                $this->addMessage('CLASSLIST_GITW_LEAVE_BLANK');
                $isValid = false;
            }
        } else {
            if (is_null($data->gitw)) {
                $this->addMessage('CLASSLIST_GITW_MISSING');
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTdo($data)
    {
        $isValid = true;

        if (!is_null($data->xferOut) || !is_null($data->withdrawCodeId)) {
            if (!is_null($data->tdo)) {
                $this->addMessage('CLASSLIST_TDO_LEAVE_BLANK');
                $isValid = false;
            }
        } else {
            if (is_null($data->tdo)) {
                $this->addMessage('CLASSLIST_TDO_MISSING');
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTeamYear($data)
    {
        $isValid = true;

        if (is_null($data->atWeekend) && is_null($data->xferIn) && is_null($data->rereg)) {
            $this->addMessage('CLASSLIST_WKND_MISSING', $data->teamYear);
            $isValid = false;
        } else {
            $cellCount = 0;
            if (!is_null($data->atWeekend)) {
                $cellCount++;
            }
            if (!is_null($data->xferIn)) {
                $cellCount++;
            }
            if (!is_null($data->rereg)) {
                $cellCount++;
            }

            if ($cellCount !== 1) {
                $this->addMessage('CLASSLIST_WKND_XIN_REREG_ONLY_ONE', $data->teamYear);
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTransfer($data)
    {
        $isValid = true;

        if (!is_null($data->xferIn) || !is_null($data->xferOut)) {

            // TODO: We probably don't need to show this every week. We need a better way to alert something for
            //       the first week.
            // Always display this message.
            $this->addMessage('CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER');

            if (is_null($data->comment)) {
                $this->addMessage('CLASSLIST_XFER_COMMENT_MISSING');
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateWithdraw($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId)) {
            if (!is_null($data->ctw)) {
                $this->addMessage('CLASSLIST_WD_CTW_ONLY_ONE');
                $isValid = false;
            }
            if (is_null($data->comment)) {
                $this->addMessage('CLASSLIST_WD_COMMENT_MISSING');
                $isValid = false;
            }
        } else if (!is_null($data->ctw)) {
            if (is_null($data->comment)) {
                $this->addMessage('CLASSLIST_CTW_COMMENT_MISSING');
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTravel($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) || !is_null($data->xferOut)) {
            return $isValid; // Not required if withdrawn
        }

        // Travel and Rooming must be reported starting after the configured date
        if ($this->isTimeToCheckTravel()) {
            if (is_null($data->travel)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->addMessage('CLASSLIST_TRAVEL_COMMENT_MISSING');
                    $isValid = false;
                } else {
                    $this->addMessage('CLASSLIST_TRAVEL_COMMENT_REVIEW');
                }
            }
            if (is_null($data->room)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->addMessage('CLASSLIST_ROOM_COMMENT_MISSING');
                    $isValid = false;
                } else {
                    $this->addMessage('CLASSLIST_ROOM_COMMENT_REVIEW');
                }
            }
        }

        return $isValid;
    }
}
