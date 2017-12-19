<?php
namespace TmlpStats\Validate\Objects;

use App;
use Cache;
use Respect\Validation\Validator as v;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Traits;

class ApiTeamMemberValidator extends ApiObjectsValidatorAbstract
{
    use Traits\ValidatesTravelWithConfig;

    const MAX_COMMENT_LENGTH = 255;

    protected $accountabilityCache = [];

    protected function populateValidators($data)
    {
        $idValidator         = v::numeric()->positive();
        $nameValidator       = v::stringType()->notEmpty();
        $boolValidator       = v::boolType();
        $boolOrNullValidator = v::optional($boolValidator);
        $teamYearValidator   = v::numeric()->between(1, 2, true);
        $numericValidator    = v::numeric()->min(0, true);

        $this->dataValidators['firstName']  = $nameValidator;
        $this->dataValidators['lastName']   = $nameValidator;
        $this->dataValidators['email']      = v::optional(v::email());
        $this->dataValidators['phone']      = v::optional(v::phone());
        $this->dataValidators['teamYear']   = $teamYearValidator;
        $this->dataValidators['atWeekend']  = $boolValidator;
        $this->dataValidators['isReviewer'] = $boolOrNullValidator;
        $this->dataValidators['xferOut']    = $boolOrNullValidator;
        $this->dataValidators['xferIn']     = $boolOrNullValidator;
        $this->dataValidators['wbo']        = $boolOrNullValidator;
        $this->dataValidators['ctw']        = $boolOrNullValidator;
        $this->dataValidators['rereg']      = $boolOrNullValidator;
        $this->dataValidators['excep']      = $boolOrNullValidator;
        $this->dataValidators['travel']     = $boolOrNullValidator;
        $this->dataValidators['room']       = $boolOrNullValidator;
        $this->dataValidators['gitw']       = $boolOrNullValidator;
        $this->dataValidators['tdo']        = v::oneOf(
            $numericValidator,
            v::boolType(),
            v::nullType()
        );
        $this->dataValidators['rppCap'] = v::optional($numericValidator);
        $this->dataValidators['rppCpc'] = v::optional($numericValidator);
        $this->dataValidators['rppLf']  = v::optional($numericValidator);
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
        if (!$this->validateAccountabilities($data)) {
            $this->isValid = false;
        }
        if (!$this->validateComment($data)) {
            $this->isValid = false;
        }
        if (!$this->validateEmail($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateGitw($data)
    {
        $isValid = true;

        if ($data->xferOut || !is_null($data->withdrawCodeId) || $data->wbo) {
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

        if ($data->xferOut || !is_null($data->withdrawCodeId) || $data->wbo) {
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

            $lastWeek = count($this->pastWeeks) ? $this->pastWeeks[0] : null;

            if ($data->xferIn && (!$lastWeek || !$lastWeek->xferIn)) {
                $this->addMessage('warning', [
                    'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                    'ref' => $data->getReference(['field' => 'xferIn']),
                ]);
            } else if ($data->xferOut && (!$lastWeek || !$lastWeek->xferOut)) {
                $this->addMessage('warning', [
                    'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                    'ref' => $data->getReference(['field' => 'xferOut']),
                ]);
            }

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

        if ((!is_null($data->withdrawCodeId) && $data->wbo)
            || (!is_null($data->withdrawCodeId) && $data->ctw)
            || ($data->wbo && $data->ctw)
        ) {
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
        } else if ($data->ctw || $data->wbo) {
            if (!$data->comment) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_CTW_WBO_COMMENT_MISSING',
                    'ref' => $data->getReference(['field' => 'comment']),
                ]);
                $isValid = false;
            }
        }

        if (!$data->withdrawCodeId) {
            return $isValid;
        }

        $code = $this->getWithdrawCode($data->withdrawCodeId);
        if (!$code) {
            $this->addMessage('error', [
                'id' => 'CLASSLIST_WD_CODE_UNKNOWN',
                'ref' => $data->getReference(['field' => 'withdrawCodeId']),
            ]);
            return false;
        }

        if (!$code->active) {
            $this->addMessage('error', [
                'id' => 'CLASSLIST_WD_CODE_INACTIVE',
                'ref' => $data->getReference(['field' => 'withdrawCodeId']),
                'params' => [
                    'reason' => $code->display,
                ],
            ]);
            return false;
        }

        if ($code->context !== 'all' && $code->context !== 'team_member') {
            $this->addMessage('error', [
                'id' => 'CLASSLIST_WD_CODE_WRONG_CONTEXT',
                'ref' => $data->getReference(['field' => 'withdrawCodeId']),
                'params' => [
                    'reason' => $code->display,
                ],
            ]);
            return false;
        }

        return $isValid;
    }

    public function validateTravel($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) || $data->xferOut || $data->wbo) {
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
            return true;
        }

        if (!is_null($data->withdrawCodeId) || $data->xferOut || $data->wbo) {
            $this->addMessage('error', [
                'id' => 'CLASSLIST_ACCOUNTABLE_AND_WITHDRAWN',
                'ref' => $data->getReference(['field' => 'accountabilities']),
            ]);
            // We don't need to ask for contact info if they shouldn't be accountable
            return false;
        }

        $hasMissingPhoneMessage = false;
        $hasMissingEmailMessage = false;

        $requiresContact = [4, 5, 6, 7, 8, 9];
        foreach ($data->accountabilities as $id) {
            $accountability = $this->getAccountability($id);

            if (!$accountability) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_UNKNOWN_ACCOUNTABILITY',
                    'ref' => $data->getReference(['field' => 'accountability']),
                    'params' => ['accountabilityId' => $id],
                ]);
                $isValid = false;
                continue;
            }

            if (!in_array($id, $requiresContact)) {
                continue;
            }

            if (!$data->phone && !$hasMissingPhoneMessage) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                    'ref' => $data->getReference(['field' => 'phone']),
                    'params' => ['accountability' => $accountability->display],
                ]);
                $isValid = false;

                // Only log one error for missing contact info
                $hasMissingPhoneMessage = true;
            }

            if (!$data->email && !$hasMissingEmailMessage) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                    'ref' => $data->getReference(['field' => 'email']),
                    'params' => ['accountability' => $accountability->display],
                ]);
                $isValid = false;

                // Only log one error for missing contact info
                $hasMissingEmailMessage = true;
            }
        }

        return $isValid;
    }

    public function validateComment($data)
    {
        if (!$data->comment) {
            return true;
        }

        $isValid = true;

        $currentLength = strlen($data->comment);
        if ($currentLength > static::MAX_COMMENT_LENGTH) {
            $this->addMessage('error', [
                'id' => 'GENERAL_COMMENT_TOO_LONG',
                'ref' => $data->getReference(['field' => 'comment']),
                'params' => [
                    'currentLength' => $currentLength,
                    'maxLength' => static::MAX_COMMENT_LENGTH,
                ],
            ]);
            $isValid = false;
        }

        return $isValid;
    }

    public function validateEmail($data)
    {
        if (!$data->email) {
            return true;
        }

        $bouncedEmails = App::make(Api\Context::class)->getSetting('bouncedEmails');
        if (!$bouncedEmails) {
            return true;
        }

        $emails = explode(',', $bouncedEmails);
        if (in_array($data->email, $emails)) {
            $this->addMessage('warning', [
                'id' => 'CLASSLIST_BOUNCED_EMAIL',
                'ref' => $data->getReference(['field' => 'email']),
                'params' => [
                    'email' => $data->email,
                ],
            ]);
        }

        return true;
    }

    public function getWithdrawCode($id)
    {
        if ($id === null) {
            return null;
        }
        return Models\WithdrawCode::find($id);
    }

    /**
     * Get accountability object
     *
     * Using a cache to avoid multiple lookups within a single report validation
     *
     * @param  integer $id Accountability Id
     * @return Models\Accountability
     */
    protected function getAccountability($id)
    {
        if (!$this->accountabilityCache) {
            $this->accountabilityCache = Cache::remember('team_accountabilities', 10, function () {
                $allAccountabilities = Models\Accountability::context('team')->get();
                return collect($allAccountabilities)->keyBy(function ($item) {
                    return $item->id;
                })->all();
            });
        }

        return isset($this->accountabilityCache[$id]) ? $this->accountabilityCache[$id] : null;
    }
}
