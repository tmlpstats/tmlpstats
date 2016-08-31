<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats\Domain;
use TmlpStats\Traits;

class ApiTeamMemberValidator extends ObjectsValidatorAbstract
{
    use Traits\ValidatesApiObjects, Traits\ValidatesTravelWithConfig;

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

        if ($data->xferOut || !is_null($data->withdrawCodeId)) {
            return $isValid; // Not required if withdrawn
        }

        if (is_null($data->gitw)) {
            $this->messages[] = Domain\ValidationMessage::error([
                'id' => 'CLASSLIST_GITW_MISSING',
                'ref' => $data->getReference(['field' => 'gitw']),
            ]);
            $isValid = false;
        }

        return $isValid;
    }

    public function validateTdo($data)
    {
        $isValid = true;

        if ($data->xferOut || !is_null($data->withdrawCodeId)) {
            return $isValid; // Not required if withdrawn
        }

        if (is_null($data->tdo)) {
            $this->messages[] = Domain\ValidationMessage::error([
                'id' => 'CLASSLIST_TDO_MISSING',
                'ref' => $data->getReference(['field' => 'tdo']),
            ]);
            $isValid = false;
        }

        return $isValid;
    }

    public function validateTeamYear($data)
    {
        $isValid = true;

        if (!$data->atWeekend && !$data->xferIn && !$data->rereg) {
            $this->messages[] = Domain\ValidationMessage::error([
                'id' => 'CLASSLIST_WKND_MISSING',
                'ref' => $data->getReference(['field' => 'atWeekend']),
            ]);
            $isValid = false;
        } else {
            $field = '';
            $setCount = 0;
            if ($data->atWeekend) {
                $setCount++;
                $field = 'atWeekend';
            }
            if ($data->xferIn) {
                $setCount++;
                $field = 'xferIn';
            }
            if ($data->rereg) {
                $setCount++;
                $field = 'rereg';
            }

            if ($setCount !== 1) {
                $this->messages[] = Domain\ValidationMessage::error([
                    'id' => 'CLASSLIST_WKND_XIN_REREG_ONLY_ONE',
                    'ref' => $data->getReference(['field' => $field]),
                ]);
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTransfer($data)
    {
        $isValid = true;

        if ($data->xferIn && $data->xferOut) {
            $this->messages[] = Domain\ValidationMessage::error([
                'id' => 'CLASSLIST_XFER_ONLY_ONE',
                'ref' => $data->getReference(['field' => 'xferIn']),
            ]);
            $isValid = false;
        }

        if ($data->xferIn || $data->xferOut) {

            // TODO: We probably don't need to show this every week. We need a better way to alert something for
            //       the first week.
            // Always display this message.
            $this->messages[] = Domain\ValidationMessage::warning([
                'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                'ref' => $data->getReference(['field' => $data->xferIn ? 'xferIn' : 'xferOut']),
            ]);

            if (is_null($data->comment)) {
                $this->messages[] = Domain\ValidationMessage::error([
                    'id' => 'CLASSLIST_XFER_COMMENT_MISSING',
                    'ref' => $data->getReference(['field' => 'comment']),
                ]);
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateWithdraw($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) && $data->ctw) {
            $this->messages[] = Domain\ValidationMessage::error([
                'id' => 'CLASSLIST_WD_CTW_ONLY_ONE',
                'ref' => $data->getReference(['field' => 'ctw']),
            ]);
            $isValid = false;
        }

        if (!is_null($data->withdrawCodeId)) {
            if (is_null($data->comment)) {
                $this->messages[] = Domain\ValidationMessage::error([
                    'id' => 'CLASSLIST_WD_COMMENT_MISSING',
                    'ref' => $data->getReference(['field' => 'comment']),
                ]);
                $isValid = false;
            }
        } else if ($data->ctw) {
            if (is_null($data->comment)) {
                $this->messages[] = Domain\ValidationMessage::error([
                    'id' => 'CLASSLIST_CTW_COMMENT_MISSING',
                    'ref' => $data->getReference(['field' => 'comment']),
                ]);
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function validateTravel($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) || $data->xferOut) {
            return $isValid; // Not required if withdrawn
        }

        // Travel and Rooming must be reported starting after the configured date
        if ($this->isTimeToCheckTravel()) {
            if (!$data->travel) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->messages[] = Domain\ValidationMessage::error([
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_MISSING',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                    $isValid = false;
                } else {
                    $this->messages[] = Domain\ValidationMessage::warning([
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_REVIEW',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                }
            }
            if (!$data->room) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->messages[] = Domain\ValidationMessage::error([
                        'id' => 'CLASSLIST_ROOM_COMMENT_MISSING',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                    $isValid = false;
                } else {
                    $this->messages[] = Domain\ValidationMessage::warning([
                        'id' => 'CLASSLIST_ROOM_COMMENT_REVIEW',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                }
            }
        }

        return $isValid;
    }
}
