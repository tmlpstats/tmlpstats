<?php
namespace TmlpStats\Validate\Objects;

use App;
use Illuminate\Support\Facades\Log;
use Respect\Validation\Validator as v;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Traits;

class ApiTeamApplicationValidator extends ApiObjectsValidatorAbstract
{
    use Traits\ValidatesTravelWithConfig;

    const MAX_DAYS_TO_SEND_APPLICATION_OUT = 3;
    const MAX_DAYS_TO_RECEIVE_APPLICATION = 7;
//    const MAX_DAYS_TO_APPROVE_APPLICATION = 7;
    const MAX_DAYS_TO_APPROVE_APPLICATION = 14;

    const MAX_COMMENT_LENGTH = 255;

    protected $startingNextQuarter = null;
    protected $nextQuarter = 'unset';

    protected function populateValidators($data)
    {
        $idValidator = v::numeric()->positive();
        $nameValidator = v::stringType()->notEmpty();
        $dateValidator = v::date('Y-m-d');
        $dateOrNullValidator = v::optional($dateValidator);
        $boolOrNullValidator = v::optional(v::boolType());

        $this->dataValidators['firstName'] = $nameValidator;
        $this->dataValidators['lastName'] = $nameValidator;
        $this->dataValidators['email'] = v::optional(v::email());
        $this->dataValidators['phone'] = v::optional(v::phone());
        $this->dataValidators['teamYear'] = v::numeric()->between(1, 2, true);
        $this->dataValidators['regDate'] = $dateValidator;
        $this->dataValidators['appOutDate'] = $dateOrNullValidator;
        $this->dataValidators['appInDate'] = $dateOrNullValidator;
        $this->dataValidators['apprDate'] = $dateOrNullValidator;
        $this->dataValidators['wdDate'] = $dateOrNullValidator;
        $this->dataValidators['travel'] = $boolOrNullValidator;
        $this->dataValidators['room'] = $boolOrNullValidator;
        $this->dataValidators['isReviewer'] = $boolOrNullValidator;
        $this->dataValidators['incomingQuarterId'] = $idValidator;
        $this->dataValidators['withdrawCodeId'] = v::optional($idValidator);
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
        if (!$this->validateComment($data)) {
            $this->isValid = false;
        }
        if (!$this->validateWithdraw($data)) {
            $this->isValid = false;
        }
        if (!$this->validateEmail($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateApprovalProcess($data)
    {
        $isValid = true;

        if (!is_null($data->withdrawCodeId) || !is_null($data->wdDate)) {
            if (is_null($data->withdrawCodeId)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_WD_CODE_MISSING',
                    'ref' => $data->getReference(['field' => 'withdrawCodeId']),
                ]);
                $isValid = false;
            }
            if (is_null($data->wdDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_WD_DATE_MISSING',
                    'ref' => $data->getReference(['field' => 'wdDate']),
                ]);
                $isValid = false;
            }
        } else if (!is_null($data->apprDate)) {
            if (is_null($data->appInDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPIN_DATE_MISSING',
                    'ref' => $data->getReference(['field' => 'appInDate']),
                ]);
                $isValid = false;
            }
            if (is_null($data->appOutDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPOUT_DATE_MISSING',
                    'ref' => $data->getReference(['field' => 'appOutDate']),
                ]);
                $isValid = false;
            }
        } else if (!is_null($data->appInDate)) {
            if (is_null($data->appOutDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPOUT_DATE_MISSING',
                    'ref' => $data->getReference(['field' => 'appOutDate']),
                ]);
                $isValid = false;
            }
        }

        if (is_null($data->committedTeamMemberId) && is_null($data->withdrawCodeId)) {
            $this->addMessage('warning', [
                'id' => 'TEAMAPP_NO_COMMITTED_TEAM_MEMBER',
                'ref' => $data->getReference(['field' => 'committedTeamMemberId']),
            ]);
        }

        return $isValid;
    }

    public function validateDates($data)
    {
        $isValid = true;

        // Make sure dates for each step make sense
        if ($data->wdDate) {
            if ($data->regDate && $data->wdDate->lt($data->regDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_WD_DATE_BEFORE_REG_DATE',
                    'ref' => $data->getReference(['field' => 'wdDate']),
                ]);
                $isValid = false;
            }
            if ($data->apprDate && $data->wdDate->lt($data->apprDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_WD_DATE_BEFORE_APPR_DATE',
                    'ref' => $data->getReference(['field' => 'wdDate']),
                ]);
                $isValid = false;
            }
            if ($data->appInDate && $data->wdDate->lt($data->appInDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_WD_DATE_BEFORE_APPIN_DATE',
                    'ref' => $data->getReference(['field' => 'wdDate']),
                ]);
                $isValid = false;
            }
            if ($data->appOutDate && $data->wdDate->lt($data->appOutDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_WD_DATE_BEFORE_APPOUT_DATE',
                    'ref' => $data->getReference(['field' => 'wdDate']),
                ]);
                $isValid = false;
            }
        }
        if ($data->apprDate) {
            if ($data->regDate && $data->apprDate->lt($data->regDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPR_DATE_BEFORE_REG_DATE',
                    'ref' => $data->getReference(['field' => 'apprDate']),
                ]);
                $isValid = false;
            }
            if ($data->appInDate && $data->apprDate->lt($data->appInDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPIN_DATE',
                    'ref' => $data->getReference(['field' => 'apprDate']),
                ]);
                $isValid = false;
            }
            if ($data->appOutDate && $data->apprDate->lt($data->appOutDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPOUT_DATE',
                    'ref' => $data->getReference(['field' => 'apprDate']),
                ]);
                $isValid = false;
            }
        }
        if ($data->appInDate) {
            if ($data->regDate && $data->appInDate->lt($data->regDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPIN_DATE_BEFORE_REG_DATE',
                    'ref' => $data->getReference(['field' => 'appInDate']),
                ]);
                $isValid = false;
            }
            if ($data->appOutDate && $data->appInDate->lt($data->appOutDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPIN_DATE_BEFORE_APPOUT_DATE',
                    'ref' => $data->getReference(['field' => 'appInDate']),
                ]);
                $isValid = false;
            }
        }
        if ($data->appOutDate) {
            if ($data->regDate && $data->appOutDate->lt($data->regDate)) {
                $this->addMessage('error', [
                    'id' => 'TEAMAPP_APPOUT_DATE_BEFORE_REG_DATE',
                    'ref' => $data->getReference(['field' => 'appOutDate']),
                ]);
                $isValid = false;
            }
        }

        $reportingDate = $this->statsReport->reportingDate;

        if (is_null($data->wdDate)) {
            // Make sure steps are taken in timely manner

            // If appOutDate is not provided, check how long since they registered
            // If it is, check if it was late this week, but only show the message on the first week
//            if ($data->regDate && $reportingDate->gte($data->regDate)
//                && ((!$data->appOutDate
//                    && $data->regDate->diffInDays($reportingDate) > static::MAX_DAYS_TO_SEND_APPLICATION_OUT)
//                || ($data->appOutDate
//                    && $reportingDate->gte($data->appOutDate)
//                    && $data->appOutDate->diffInDays($data->regDate) > static::MAX_DAYS_TO_SEND_APPLICATION_OUT
//                    && $data->appOutDate->diffInDays($reportingDate) <= 7))
//            ) {
//                $this->addMessage('warning', [
//                    'id' => 'TEAMAPP_APPOUT_LATE',
//                    'ref' => $data->getReference(['field' => 'appOutDate']),
//                    'params' => ['daysSince' => static::MAX_DAYS_TO_SEND_APPLICATION_OUT],
//                ]);
//            }

            // If appInDate is not provided, check how long since they registered
            // If it is, check if it was late this week, but only show the message on the first week
//            if ($data->appOutDate && $reportingDate->gte($data->appOutDate)
//                && ((!$data->appInDate
//                    && $data->appOutDate->diffInDays($reportingDate) > static::MAX_DAYS_TO_RECEIVE_APPLICATION)
//                || ($data->appInDate
//                    && $reportingDate->gte($data->appInDate)
//                    && $data->appInDate->diffInDays($data->appOutDate) > static::MAX_DAYS_TO_RECEIVE_APPLICATION
//                    && $data->appInDate->diffInDays($reportingDate) <= 7))
//            ) {
//                $this->addMessage('warning', [
//                    'id' => 'TEAMAPP_APPIN_LATE',
//                    'ref' => $data->getReference(['field' => 'appInDate']),
//                    'params' => ['daysSince' => static::MAX_DAYS_TO_RECEIVE_APPLICATION],
//                ]);
//            }

            // If apprDate is not provided, check how long since they registered
            // If it is, check if it was late this week, but only show the message on the first week
//            if ($data->appInDate && $reportingDate->gte($data->appInDate)
//                && ((!$data->apprDate
//                    && $data->appInDate->diffInDays($reportingDate) > static::MAX_DAYS_TO_APPROVE_APPLICATION)
//                || ($data->apprDate
//                    && $reportingDate->gte($data->apprDate)
//                    && $data->apprDate->diffInDays($data->appInDate) > static::MAX_DAYS_TO_APPROVE_APPLICATION
//                    && $data->apprDate->diffInDays($reportingDate) <= 7))
//            ) {
//                $this->addMessage('warning', [
//                    'id' => 'TEAMAPP_APPR_LATE',
//                    'ref' => $data->getReference(['field' => 'apprDate']),
//                    'params' => ['daysSince' => static::MAX_DAYS_TO_APPROVE_APPLICATION],
//                ]);
//            }


            // If it is, check if it was late this week, but only show the message on the first week
            if ($data->appOutDate && $reportingDate->gte($data->appOutDate)
                && (
                    (
                        !$data->apprDate
                        && $data->appOutDate->diffInDays($reportingDate) > static::MAX_DAYS_TO_APPROVE_APPLICATION
                    )
                    ||
                    (
                        $data->apprDate
                        && $reportingDate->gte($data->apprDate)
                        && $data->apprDate->diffInDays($data->appOutDate) > static::MAX_DAYS_TO_APPROVE_APPLICATION
                        && $data->apprDate->diffInDays($reportingDate) < 7
                    )
                )
            ) {
                $this->addMessage('warning', [
                    'id' => 'TEAMAPP_APPR_LATE2',
                    'ref' => $data->getReference(['field' => 'apprDate']),
                    'params' => ['daysSince' => static::MAX_DAYS_TO_APPROVE_APPLICATION],
                ]);
            }
        }

        // Make sure dates are in the past
        if (!is_null($data->regDate) && $reportingDate->lt($data->regDate)) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_REG_DATE_IN_FUTURE',
                'ref' => $data->getReference(['field' => 'regDate']),
            ]);
            $isValid = false;
        }
        if (!is_null($data->wdDate) && $reportingDate->lt($data->wdDate)) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_WD_DATE_IN_FUTURE',
                'ref' => $data->getReference(['field' => 'wdDate']),
            ]);
            $isValid = false;
        }
        if (!is_null($data->apprDate) && $reportingDate->lt($data->apprDate)) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_APPR_DATE_IN_FUTURE',
                'ref' => $data->getReference(['field' => 'apprDate']),
            ]);
            $isValid = false;
        }
        if (!is_null($data->appInDate) && $reportingDate->lt($data->appInDate)) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_APPIN_DATE_IN_FUTURE',
                'ref' => $data->getReference(['field' => 'appInDate']),
            ]);
            $isValid = false;
        }
        if (!is_null($data->appOutDate) && $reportingDate->lt($data->appOutDate)) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_APPOUT_DATE_IN_FUTURE',
                'ref' => $data->getReference(['field' => 'appOutDate']),
            ]);
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
            if (!$data->travel) {
                // Error if no comment provided, warning to look at it otherwise
                if (!$data->comment) {
                    $this->addMessage('error', [
                        'id' => 'TEAMAPP_TRAVEL_COMMENT_MISSING',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                    $isValid = false;
                } else {
                    $this->addMessage('warning', [
                        'id' => 'TEAMAPP_TRAVEL_COMMENT_REVIEW',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                }
            }
            if (!$data->room) {
                // Error if no comment provided, warning to look at it otherwise
                if (!$data->comment) {
                    $this->addMessage('error', [
                        'id' => 'TEAMAPP_ROOM_COMMENT_MISSING',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                    $isValid = false;
                } else {
                    $this->addMessage('warning', [
                        'id' => 'TEAMAPP_ROOM_COMMENT_REVIEW',
                        'ref' => $data->getReference(['field' => 'comment']),
                    ]);
                }
            }
        }

        return $isValid;
    }

    public function validateReviewer($data)
    {
        $isValid = true;

        if ($data->isReviewer && $data->teamYear !== 2) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_REVIEWER_TEAM1',
                'ref' => $data->getReference(['field' => 'isReviewer']),
            ]);
            $isValid = false;
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

    public function validateWithdraw($data)
    {
        if (!$data->withdrawCodeId) {
            return true;
        }

        $code = $this->getWithdrawCode($data->withdrawCodeId);
        if (!$code) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_WD_CODE_UNKNOWN',
                'ref' => $data->getReference(['field' => 'withdrawCodeId']),
            ]);
            return false;
        }

        if (!$code->active) {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_WD_CODE_INACTIVE',
                'ref' => $data->getReference(['field' => 'withdrawCodeId']),
                'params' => [
                    'reason' => $code->display,
                ],
            ]);
            return false;
        }

        if ($code->context !== 'all' && $code->context !== 'application') {
            $this->addMessage('error', [
                'id' => 'TEAMAPP_WD_CODE_WRONG_CONTEXT',
                'ref' => $data->getReference(['field' => 'withdrawCodeId']),
                'params' => [
                    'reason' => $code->display,
                ],
            ]);
            return false;
        }

        return true;
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
                'id' => 'TEAMAPP_BOUNCED_EMAIL',
                'ref' => $data->getReference(['field' => 'email']),
                'params' => [
                    'email' => $data->email,
                ],
            ]);
        }

        return true;
    }

    public function isStartingNextQuarter($data)
    {
        if ($this->nextQuarter === 'unset') {
            $this->nextQuarter = $this->statsReport->quarter->getNextQuarter();
        }

        if (!$this->nextQuarter) {
            return false;
        }

        return $this->startingNextQuarter = ($this->nextQuarter->id === $data->incomingQuarterId);
    }

    public function getWithdrawCode($id)
    {
        if ($id === null) {
            return null;
        }
        return Models\WithdrawCode::find($id);
    }
}
