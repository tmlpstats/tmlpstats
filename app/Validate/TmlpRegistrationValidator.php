<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Import\Xlsx\Reader as Reader;
use Respect\Validation\Validator as v;

class TmlpRegistrationValidator extends ValidatorAbstract
{
    const MAX_DAYS_TO_SEND_APPLICATION_OUT = 2;
    const MAX_DAYS_TO_APPROVE_APPLICATION = 14;

    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function populateValidators($data)
    {
        $nameValidator               = v::string()->notEmpty();
        $dateValidator               = v::date('Y-m-d');
        $dateOrNullValidator         = v::when(v::nullValue(), v::alwaysValid(), $dateValidator);
        $yesValidator                = v::string()->regex('/^[Y]$/i');
        $yesOrNullValidator          = v::when(v::nullValue(), v::alwaysValid(), $yesValidator);

        $weekendRegTypes = array(
            'before',
            'during',
            'after',
        );

        $incomingWeekendTypes = array(
            'current',
            'future',
        );

        $wdTypes = array(
            '1 AP',
            '1 NW',
            '1 FIN',
            '1 FW',
            '1 MOA',
            '1 NA',
            '1 OOC',
            '1 T',
            '1 RE',
            '1 WB',
            '2 AP',
            '2 NW',
            '2 FIN',
            '2 FW',
            '2 MOA',
            '2 NA',
            '2 OOC',
            '2 T',
            '2 RE',
            '2 WB',
            'R AP',
            'R NW',
            'R FIN',
            'R FW',
            'R MOA',
            'R NA',
            'R OOC',
            'R T',
            'R RE',
            'R WB',
        );

        $incomingTeamYearValidator = v::numeric()->between(1, 2, true);

        if ($data->incomingTeamYear == 1) {
            $indicator = 1;
        } else {
            $indicator = 2;
            if ($data->bef == 'R' || $data->dur == 'R' || $data->aft == 'R') {
                $indicator = 'R';
            }
        }
        $equalsIncomingYearValidator = v::when(v::nullValue(), v::alwaysValid(), v::equals($indicator));

        $this->dataValidators['firstName']               = $nameValidator;
        $this->dataValidators['lastName']                = $nameValidator;
        $this->dataValidators['weekendReg']              = v::in($weekendRegTypes);
        $this->dataValidators['incomingWeekend']         = v::in($incomingWeekendTypes);
        $this->dataValidators['incomingTeamYear']        = $incomingTeamYearValidator;
        $this->dataValidators['bef']                     = $equalsIncomingYearValidator;
        $this->dataValidators['dur']                     = $equalsIncomingYearValidator;
        $this->dataValidators['aft']                     = $equalsIncomingYearValidator;
        $this->dataValidators['appOut']                  = $equalsIncomingYearValidator;
        $this->dataValidators['appIn']                   = $equalsIncomingYearValidator;
        $this->dataValidators['appr']                    = $equalsIncomingYearValidator;
        $this->dataValidators['wd']                      = v::when(v::nullValue(), v::alwaysValid(), v::in($wdTypes));
        $this->dataValidators['regDate']                 = $dateValidator;
        $this->dataValidators['appOutDate']              = $dateOrNullValidator;
        $this->dataValidators['appInDate']               = $dateOrNullValidator;
        $this->dataValidators['apprDate']                = $dateOrNullValidator;
        $this->dataValidators['wdDate']                  = $dateOrNullValidator;
        $this->dataValidators['committedTeamMemberName'] = v::when(v::nullValue(), v::alwaysValid(), $nameValidator);
        $this->dataValidators['travel']                  = $yesOrNullValidator;
        $this->dataValidators['room']                    = $yesOrNullValidator;
    }

    protected function validate($data)
    {
        if (!$this->validateWeekendReg($data)) {
            $this->isValid = false;
        }
        if (!$this->validateApprovalProcess($data)) {
            $this->isValid = false;
        }
        if (!$this->validateDates($data)) {
            $this->isValid = false;
        }
        if (!$this->validateComment($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTravel($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateWeekendReg($data)
    {
        $isValid = true;

        if ((!is_null($data->bef) && !is_null($data->dur))
            || (!is_null($data->bef) && !is_null($data->aft))
            || (!is_null($data->dur) && !is_null($data->aft))
        ) {
            $this->addMessage('TMLPREG_MULTIPLE_WEEKENDREG', $data->incomingTeamYear);
            $isValid = false;
        }

        return $isValid;
    }

    public function validateApprovalProcess($data)
    {
        $isValid = true;

        if (!is_null($data->wd) || !is_null($data->wdDate)) {
            $col = 'wd';
            if (is_null($data->wd)) {
                $this->addMessage('TMLPREG_WD_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
            if (is_null($data->wdDate)) {
                $this->addMessage('TMLPREG_WD_DATE_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }

            $value = $data->wd;
            $weekendRegType = $this->getWeekendReg($data);
            if ($value[0] != $weekendRegType) {
                $this->addMessage('TMLPREG_WD_DOESNT_MATCH_INCOMING_YEAR');
                $isValid = false;
            }
            if (!is_null($data->appOut) || !is_null($data->appIn) || !is_null($data->appr)) {
                $this->addMessage('TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', $col, $data->incomingTeamYear);
                $isValid = false;
            }
        } else if (!is_null($data->appr) || !is_null($data->apprDate)) {
            $col = 'appr';
            if (is_null($data->appr)) {
                $this->addMessage('TMLPREG_APPR_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
            if (is_null($data->apprDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
            if (is_null($data->appInDate)) {
                $this->addMessage('TMLPREG_APPR_MISSING_APPIN_DATE');
                $isValid = false;
            }
            if (is_null($data->appOutDate)) {
                $this->addMessage('TMLPREG_APPR_MISSING_APPOUT_DATE');
                $isValid = false;
            }

            if (!is_null($data->appOut) || !is_null($data->appIn)) {
                $this->addMessage('TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR', $col, $data->incomingTeamYear);
                $isValid = false;
            }
        } else if (!is_null($data->appIn) || !is_null($data->appInDate)) {
            $col = 'in';
            if (is_null($data->appIn)) {
                $this->addMessage('TMLPREG_APPIN_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
            if (is_null($data->appInDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
            if (is_null($data->appOutDate)) {
                $this->addMessage('TMLPREG_APPIN_MISSING_APPOUT_DATE');
                $isValid = false;
            }

            if (!is_null($data->appOut)) {
                $this->addMessage('TMLPREG_APPIN_ONLY_ONE_YEAR_INDICATOR', $col, $data->incomingTeamYear);
                $isValid = false;
            }
        } else if (!is_null($data->appOut) || !is_null($data->appOutDate)) {
            $col = 'out';
            if (is_null($data->appOut)) {
                $this->addMessage('TMLPREG_APPOUT_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
            if (is_null($data->appOutDate)) {
                $this->addMessage('TMLPREG_APPOUT_DATE_MISSING', $col, $data->incomingTeamYear);
                $isValid = false;
            }
        }

        if (is_null($data->committedTeamMemberName) && is_null($data->wd)) {
            $this->addMessage('TMLPREG_NO_COMMITTED_TEAM_MEMBER');
            $isValid = false;
        }

        return $isValid;
    }

    public function validateDates($data)
    {
        $isValid = true;

        $statsReport = $this->getStatsReport();

        // Get Date objects from date strings
        $regDate    = is_null($data->regDate) ? null : $this->getDateObject($data->regDate);
        $appInDate  = is_null($data->appInDate) ? null : $this->getDateObject($data->appInDate);
        $appOutDate = is_null($data->appOutDate) ? null : $this->getDateObject($data->appOutDate);
        $apprDate   = is_null($data->apprDate) ? null : $this->getDateObject($data->apprDate);
        $wdDate     = is_null($data->wdDate) ? null : $this->getDateObject($data->wdDate);

        // Make sure dates for each step make sense
        if (!is_null($data->wdDate) && $wdDate) {
            if ($regDate && $wdDate->lt($regDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
            if (!is_null($data->apprDate) && $apprDate && $wdDate->lt($apprDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPR_DATE');
                $isValid = false;
            }
            if (!is_null($data->appInDate) && $appInDate && $wdDate->lt($appInDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPIN_DATE');
                $isValid = false;
            }
            if (!is_null($data->appOutDate) && $wdDate->lt($appOutDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPOUT_DATE');
                $isValid = false;
            }
        }
        if (!is_null($data->apprDate) && $apprDate) {
            if ($regDate && $apprDate->lt($regDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
            if (!is_null($data->appInDate) && $appInDate && $apprDate->lt($appInDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_APPIN_DATE');
                $isValid = false;
            }
            if (!is_null($data->appOutDate) && $appOutDate && $apprDate->lt($appOutDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE');
                $isValid = false;
            }
        }
        if (!is_null($data->appInDate) && $appInDate) {
            if ($regDate && $appInDate->lt($regDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
            if (!is_null($data->appOutDate) && $appOutDate && $appInDate->lt($appOutDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE');
                $isValid = false;
            }
        }
        if (!is_null($data->appOutDate) && $appOutDate) {
            if ($regDate && $appOutDate->lt($regDate)) {
                $this->addMessage('TMLPREG_APPOUT_DATE_BEFORE_REG_DATE');
                $isValid = false;
            }
        }

        // Make sure Weekend Reg fields match registration date
        if ($data->incomingWeekend == 'current') {
            $dateStr = $statsReport->quarter->startWeekendDate->format('M d, Y');
            if (!is_null($data->bef) && $regDate && $regDate->gt($statsReport->quarter->startWeekendDate)) {
                $this->addMessage('TMLPREG_BEF_REG_DATE_NOT_BEFORE_WEEKEND', $dateStr, $data->bef);
                $isValid = false;
            }
            if (!is_null($data->dur) && $regDate && $regDate->diffInDays($statsReport->quarter->startWeekendDate) > 3) {
                $this->addMessage('TMLPREG_DUR_REG_DATE_NOT_DURING_WEEKEND', $dateStr, $data->dur);
                $isValid = false;
            }
            if (!is_null($data->aft) && $regDate && $regDate->lte($statsReport->quarter->startWeekendDate)) {
                $this->addMessage('TMLPREG_AFT_REG_DATE_NOT_AFTER_WEEKEND', $dateStr, $data->aft);
                $isValid = false;
            }
        }

        $maxAppOutDays = static::MAX_DAYS_TO_SEND_APPLICATION_OUT;
        $maxApplicationDays = static::MAX_DAYS_TO_APPROVE_APPLICATION;

        if (is_null($data->wdDate)){
            // Make sure steps are taken in timely manner
            if (is_null($data->appOutDate)) {
                if ($regDate && $regDate->lt($statsReport->reportingDate) && $regDate->diffInDays($statsReport->reportingDate) > $maxAppOutDays) {
                    $this->addMessage('TMLPREG_APPOUT_LATE', $maxAppOutDays);
                }
            } else if (is_null($data->appInDate)) {
                if ($appOutDate && $appOutDate->lt($statsReport->reportingDate) && $appOutDate->diffInDays($statsReport->reportingDate) > $maxApplicationDays) {
                    $this->addMessage('TMLPREG_APPIN_LATE', $maxApplicationDays);
                }
            } else if (is_null($data->apprDate)) {
                if ($appInDate && $appInDate->lt($statsReport->reportingDate) && $appInDate->diffInDays($statsReport->reportingDate) > $maxApplicationDays) {
                    $this->addMessage('TMLPREG_APPR_LATE', $maxApplicationDays);
                }
            }
        }

        // Make sure dates are in the past
        if (!is_null($data->regDate) && $regDate && $statsReport->reportingDate->lt($regDate)) {
            $this->addMessage('TMLPREG_REG_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->wdDate) && $wdDate && $statsReport->reportingDate->lt($wdDate)) {
            $this->addMessage('TMLPREG_WD_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->apprDate) && $apprDate && $statsReport->reportingDate->lt($apprDate)) {
            $this->addMessage('TMLPREG_APPR_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->appInDate) && $appInDate && $statsReport->reportingDate->lt($appInDate)) {
            $this->addMessage('TMLPREG_APPIN_DATE_IN_FUTURE');
            $isValid = false;
        }
        if (!is_null($data->appOutDate) && $appOutDate && $statsReport->reportingDate->lt($appOutDate)) {
            $this->addMessage('TMLPREG_APPOUT_DATE_IN_FUTURE');
            $isValid = false;
        }

        return $isValid;
    }

    public function validateComment($data)
    {
        $isValid = true;

        if (!is_null($data->wd)) {
            return $isValid; // Not required if withdrawn
        }

        if (is_null($data->comment) && $data->incomingWeekend == 'future') {
            $this->addMessage('TMLPREG_COMMENT_MISSING_FUTURE_WEEKEND');
            $isValid = false;
        }

        // For travel and room comment checks see validateTravel()
        return $isValid;
    }

    public function validateTravel($data)
    {
        $isValid = true;

        if (!is_null($data->wd) || $data->incomingWeekend == 'future') {
            return $isValid; // Not required if withdrawn or future registration
        }

        $statsReport = $this->getStatsReport();
        if ($statsReport->reportingDate->gt($statsReport->quarter->classroom2Date)) {
            if (is_null($data->travel)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->addMessage('TMLPREG_TRAVEL_COMMENT_MISSING');
                    $isValid = false;
                } else {
                    $this->addMessage('TMLPREG_TRAVEL_COMMENT_REVIEW');
                }
            }
            if (is_null($data->room)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($data->comment)) {
                    $this->addMessage('TMLPREG_ROOM_COMMENT_MISSING');
                    $isValid = false;
                } else {
                    $this->addMessage('TMLPREG_ROOM_COMMENT_REVIEW');
                }
            }

            // Any approved incoming without travel AND rooming booked by 2 weeks before the end of the quarter
            // is considered in a Conversation To Withdraw
            $twoWeeksBeforeWeekend = $statsReport->quarter->endWeekendDate->subWeeks(2);
            if ($statsReport->reportingDate->gte($twoWeeksBeforeWeekend) && !is_null($data->appr)) {
                if (is_null($data->travel) || is_null($data->room)) {
                    $this->addMessage('TMLPREG_TRAVEL_ROOM_CTW_COMMENT_REVIEW');
                }
            }
        }

        return $isValid;
    }

    public function getWeekendReg($data)
    {
        if (!is_null($data->bef)){
            return $data->bef;
        } else if (!is_null($data->aft)){
            return $data->aft;
        } else {
            return $data->dur;
        }
    }
}
