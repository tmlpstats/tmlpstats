<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats as Models;
use TmlpStats\Traits;

class ApiTeamMemberValidator extends ApiObjectsValidatorAbstract
{
    use Traits\ValidatesTravelWithConfig;

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
        // TODO: enable and add unit tests
        // if (!$this->validateAccountabilities($data)) {
        //     $this->isValid = false;
        // }

        return $this->isValid;
    }

    public function validateGitw($data)
    {
        $isValid = true;

        if ($data->xferOut || !is_null($data->withdrawCodeId)) {
            return $isValid; // Not required if withdrawn
        }

        if (is_null($data->gitw)) {
            $this->addMessage('error', [
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
            $this->addMessage('error', [
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
            $this->addMessage('error', [
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
                $this->addMessage('error', [
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
            $this->addMessage('error', [
                'id' => 'CLASSLIST_XFER_ONLY_ONE',
                'ref' => $data->getReference(['field' => 'xferIn']),
            ]);
            $isValid = false;
        }

        if ($data->xferIn || $data->xferOut) {

            // TODO: We probably don't need to show this every week. We need a better way to alert something for
            //       the first week.
            // Always display this message.
            $this->addMessage('warning', [
                'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                'ref' => $data->getReference(['field' => $data->xferIn ? 'xferIn' : 'xferOut']),
            ]);

            if (!$data->comment) {
                $this->addMessage('error', [
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
            $this->addMessage('error', [
                'id' => 'CLASSLIST_WD_CTW_ONLY_ONE',
                'ref' => $data->getReference(['field' => 'ctw']),
            ]);
            $isValid = false;
        }

        if (!is_null($data->withdrawCodeId)) {
            if (!$data->comment) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_WD_COMMENT_MISSING',
                    'ref' => $data->getReference(['field' => 'comment']),
                ]);
                $isValid = false;
            }
        } else if ($data->ctw) {
            if (!$data->comment) {
                $this->addMessage('error', [
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
                if (!$data->comment) {
                    $this->addMessage('error', [
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_MISSING',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                    $isValid = false;
                } else {
                    $this->addMessage('warning', [
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_REVIEW',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                }
            }
            if (!$data->room) {
                // Error if no comment provided, warning to look at it otherwise
                if (!$data->comment) {
                    $this->addMessage('error', [
                        'id' => 'CLASSLIST_ROOM_COMMENT_MISSING',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                    $isValid = false;
                } else {
                    $this->addMessage('warning', [
                        'id' => 'CLASSLIST_ROOM_COMMENT_REVIEW',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                }
            }
        }

        return $isValid;
    }

    public function validateAccountabilities($data)
    {
        $isValid = true;
        if (!$data->accountabilities) {
            return $isValid;
        }

        if (!is_null($data->withdrawCodeId) || $data->xferOut) {
            $this->addMessage('error', [
                'id' => 'CLASSLIST_ACCOUNTABLE_AND_WITHDRAWN',
                'ref' => $data->getReference(['field' => 'email']),
            ]);
            // We don't need to ask for contact info if they shouldn't be accountable
            return false;
        }

        $requiresContact = [4, 5, 6, 7, 8, 9];
        foreach ($data->accountabilities as $accountability) {
            if (in_array($accountability, $requiresContact)) {
                if (!$data->phone) {
                    $this->addMessage('error', [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'ref' => $data->getReference(['field' => 'phone']),
                        'params' => ['accountability' => Models\Accountability::find($accountability)->display],
                    ]);
                    $isValid = false;
                }

                if (!$data->email) {
                    $this->addMessage('error', [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'ref' => $data->getReference(['field' => 'email']),
                        'params' => ['accountability' => Models\Accountability::find($accountability)->display],
                    ]);
                    $isValid = false;
                }

                if (!$isValid) {
                    // we only need one message
                    break;
                }
            }
        }

        return $isValid;
    }
}
