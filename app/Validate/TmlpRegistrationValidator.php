<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\Reader as Reader;
use Respect\Validation\Validator as v;

class TmlpRegistrationValidator extends ValidatorAbstract
{
    const MAX_DAYS_TO_SEND_APPLICATION_OUT = 2;
    const MAX_DAYS_TO_APPROVE_APPLICATION = 14;

    protected $classDisplayName = 'Current Weekly Stats';

    protected function populateValidators()
    {
        $nameValidator               = v::string()->notEmpty();
        $rowIdValidator              = v::numeric()->positive();
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

        if ($this->data->incomingTeamYear == 1) {
            $indicator = 1;
        } else {
            $indicator = 2;
            if ($this->data->bef == 'R' || $this->data->dur == 'R' || $this->data->aft == 'R') {
                $indicator = 'R';
            }
        }
        $equalsIncomingYearValidator = v::when(v::nullValue(), v::alwaysValid(), v::equals($indicator));

        $this->dataValidators['firstName']               = $nameValidator;
        $this->dataValidators['lastName']                = $nameValidator;
        $this->dataValidators['weekendReg']              = v::in($weekendRegTypes);
        $this->dataValidators['incomingWeekend']         = v::in($incomingWeekendTypes);
        $this->dataValidators['incomingTeamYear']        = $incomingTeamYearValidator;
        $this->dataValidators['isReviewer']              = v::numeric()->between(0, 1, true);
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
        $this->dataValidators['tmlpRegistrationId']      = $rowIdValidator;
        $this->dataValidators['statsReportId']           = $rowIdValidator;
    }

    protected function validate()
    {
        $this->validateWeekendReg();
        $this->validateApprovalProcess();
        $this->validateDates();
        $this->validateComment();
        $this->validateTravel();

        return $this->isValid;
    }

    protected function validateWeekendReg()
    {
        if ((!is_null($this->data->bef) && !is_null($this->data->dur))
            || (!is_null($this->data->bef) && !is_null($this->data->aft))
            || (!is_null($this->data->dur) && !is_null($this->data->aft))
        ) {
            $this->addMessage('TMLPREG_MULTIPLE_WEEKENDREG', $this->data->incomingTeamYear);
            $this->isValid = false;
        }
    }

    protected function validateApprovalProcess()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wdDate)) {
            $col = 'wd';
            if (is_null($this->data->wd)) {
                $this->addMessage('TMLPREG_WD_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
            if (is_null($this->data->wdDate)) {
                $this->addMessage('TMLPREG_WD_DATE_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }

            $value = $this->data->wd;
            $weekendRegType = $this->getWeekendReg();
            if ($value[0] != $weekendRegType) {
                $this->addMessage('TMLPREG_WD_DOESNT_MATCH_INCOMING_YEAR');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOut) || !is_null($this->data->appIn) || !is_null($this->data->appr)) {
                $this->addMessage('TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
        } else if (!is_null($this->data->appr) || !is_null($this->data->apprDate)) {
            $col = 'appr';
            if (is_null($this->data->appr)) {
                $this->addMessage('TMLPREG_APPR_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
            if (is_null($this->data->apprDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
            if (is_null($this->data->appInDate)) {
                $this->addMessage('TMLPREG_APPR_MISSING_APPIN_DATE');
                $this->isValid = false;
            }
            if (is_null($this->data->appOutDate)) {
                $this->addMessage('TMLPREG_APPR_MISSING_APPOUT_DATE');
                $this->isValid = false;
            }

            if (!is_null($this->data->appOut) || !is_null($this->data->appIn)) {
                $this->addMessage('TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
        } else if (!is_null($this->data->appIn) || !is_null($this->data->appInDate)) {
            $col = 'in';
            if (is_null($this->data->appIn)) {
                $this->addMessage('TMLPREG_APPIN_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
            if (is_null($this->data->appInDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
            if (is_null($this->data->appOutDate)) {
                $this->addMessage('TMLPREG_APPIN_MISSING_APPOUT_DATE');
                $this->isValid = false;
            }

            if (!is_null($this->data->appOut)) {
                $this->addMessage('TMLPREG_APPIN_ONLY_ONE_YEAR_INDICATOR', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
        } else if (!is_null($this->data->appOut) || !is_null($this->data->appOutDate)) {
            $col = 'out';
            if (is_null($this->data->appOut)) {
                $this->addMessage('TMLPREG_APPOUT_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
            if (is_null($this->data->appOutDate)) {
                $this->addMessage('TMLPREG_APPOUT_DATE_MISSING', $col, $this->data->incomingTeamYear);
                $this->isValid = false;
            }
        }

        if (is_null($this->data->committedTeamMemberName) && is_null($this->data->wd)) {
            $this->addMessage('TMLPREG_NO_COMMITTED_TEAM_MEMBER');
            $this->isValid = false;
        }
    }

    protected function validateDates()
    {
        $statsReport = $this->getStatsReport();

        // Get Date objects from date strings
        $regDate    = is_null($this->data->regDate) ? null : $this->getDateObject($this->data->regDate);
        $appInDate  = is_null($this->data->appInDate) ? null : $this->getDateObject($this->data->appInDate);
        $appOutDate = is_null($this->data->appOutDate) ? null : $this->getDateObject($this->data->appOutDate);
        $apprDate   = is_null($this->data->apprDate) ? null : $this->getDateObject($this->data->apprDate);
        $wdDate     = is_null($this->data->wdDate) ? null : $this->getDateObject($this->data->wdDate);

        // Make sure dates for each step are chronological
        if (!is_null($this->data->wdDate)) {
            if ($wdDate->lt($regDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_REG_DATE');
                $this->isValid = false;
            }
            if (!is_null($this->data->apprDate) && $wdDate->lt($apprDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPR_DATE');
                $this->isValid = false;
            }
            if (!is_null($this->data->appInDate) && $wdDate->lt($appInDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPIN_DATE');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOutDate) && $wdDate->lt($appOutDate)) {
                $this->addMessage('TMLPREG_WD_DATE_BEFORE_APPOUT_DATE');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->apprDate)) {
            if ($apprDate->lt($regDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_REG_DATE');
                $this->isValid = false;
            }
            if (!is_null($this->data->appInDate) && $apprDate->lt($appInDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_APPIN_DATE');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOutDate) && $apprDate->lt($appOutDate)) {
                $this->addMessage('TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->appInDate)) {
            if ($appInDate->lt($regDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_BEFORE_REG_DATE');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOutDate) && $appInDate->lt($appOutDate)) {
                $this->addMessage('TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->appOutDate)) {
            if ($appOutDate->lt($regDate)) {
                $this->addMessage('TMLPREG_APPOUT_DATE_BEFORE_REG_DATE');
                $this->isValid = false;
            }
        }

        // Make sure Weekend Reg fields match registration date
        if ($this->data->incomingWeekend == 'current') {
            $dateStr = $statsReport->quarter->startWeekendDate->format('M d, Y');
            if (!is_null($this->data->bef) && $regDate->gt($statsReport->quarter->startWeekendDate)) {
                $this->addMessage('TMLPREG_BEF_REG_DATE_NOT_BEFORE_WEEKEND', $dateStr, $this->data->bef);
                $this->isValid = false;
            }
            if (!is_null($this->data->dur) && $regDate->diffInDays($statsReport->quarter->startWeekendDate) > 3) {
                $this->addMessage('TMLPREG_DUR_REG_DATE_NOT_DURING_WEEKEND', $dateStr, $$this->data->dur);
                $this->isValid = false;
            }
            if (!is_null($this->data->aft) && $regDate->lte($statsReport->quarter->startWeekendDate)) {
                $this->addMessage('TMLPREG_AFT_REG_DATE_NOT_AFTER_WEEKEND', $dateStr, $this->data->aft);
                $this->isValid = false;
            }
        }

        $maxAppOutDays = static::MAX_DAYS_TO_SEND_APPLICATION_OUT;
        $maxApplicationDays = static::MAX_DAYS_TO_APPROVE_APPLICATION;
        if (is_null($this->data->wdDate)){
            // Make sure steps are taken according to design
            if (is_null($this->data->appOutDate)) {
                if ($regDate->diffInDays($statsReport->reportingDate) > $maxAppOutDays) {
                    $this->addMessage('TMLPREG_APPOUT_LATE', $maxAppOutDays);
                }
            } else if (is_null($this->data->appInDate)) {
                if ($appOutDate->diffInDays($statsReport->reportingDate) > $maxApplicationDays) {
                    $this->addMessage('TMLPREG_APPIN_LATE', $maxApplicationDays);
                }
            } else if (is_null($this->data->apprDate)) {
                if ($appOutDate->diffInDays($statsReport->reportingDate) > $maxApplicationDays) {
                    $this->addMessage('TMLPREG_APPR_LATE', $maxApplicationDays);
                }
            }
        }

        // Make sure dates are in the past
        if (!is_null($this->data->regDate) && $statsReport->reportingDate->lt($regDate)) {
            $this->addMessage('TMLPREG_REG_DATE_IN_FUTURE');
            $this->isValid = false;
        }
        if (!is_null($this->data->wdDate) && $statsReport->reportingDate->lt($wdDate)) {
            $this->addMessage('TMLPREG_WD_DATE_IN_FUTURE');
            $this->isValid = false;
        }
        if (!is_null($this->data->apprDate) && $statsReport->reportingDate->lt($apprDate)) {
            $this->addMessage('TMLPREG_APPR_DATE_IN_FUTURE');
            $this->isValid = false;
        }
        if (!is_null($this->data->appInDate) && $statsReport->reportingDate->lt($appInDate)) {
            $this->addMessage('TMLPREG_APPIN_DATE_IN_FUTURE');
            $this->isValid = false;
        }
        if (!is_null($this->data->appOutDate) && $statsReport->reportingDate->lt($appOutDate)) {
            $this->addMessage('TMLPREG_APPOUT_DATE_IN_FUTURE');
            $this->isValid = false;
        }
    }

    protected function validateComment()
    {
        if (!is_null($this->data->wd)) {
            return; // Not required if withdrawn
        }

        if (is_null($this->data->comment) && $this->data->incomingWeekend == 'future') {
            $this->addMessage('TMLPREG_COMMENT_MISSING_FUTURE_WEEKEND');
            $this->isValid = false;
        }

        // For travel and room comment checks see validateTravel()
    }

    protected function validateTravel()
    {
        if (!is_null($this->data->wd) || $this->data->incomingWeekend == 'future') {
            return; // Not required if withdrawn or future registration
        }

        $statsReport = $this->getStatsReport();
        if ($statsReport->reportingDate->gt($statsReport->quarter->classroom2Date)) {
            if (is_null($this->data->travel)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($this->data->comment)) {
                    $this->addMessage('TMLPREG_TRAVEL_COMMENT_MISSING');
                    $this->isValid = false;
                } else {
                    $this->addMessage('TMLPREG_TRAVEL_COMMENT_REVIEW');
                }
            }
            if (is_null($this->data->room)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($this->data->comment)) {
                    $this->addMessage('TMLPREG_ROOM_COMMENT_MISSING');
                    $this->isValid = false;
                } else {
                    $this->addMessage('TMLPREG_ROOM_COMMENT_REVIEW');
                }
            }

            // Any incoming without travel AND rooming booked by 2 weeks before the end of the quarter
            // is considered in a Conversation To Withdraw
            $twoWeeksBeforeWeekend = $statsReport->quarter->endWeekendDate->subWeeks(3);
            if ($statsReport->reportingDate->gte($twoWeeksBeforeWeekend)) {
                if (is_null($this->data->travel) || is_null($this->data->room)) {
                    $this->addMessage('TMLPREG_TRAVEL_ROOM_CTW_COMMENT_REVIEW');
                }
            }
        }
    }

    protected function getWeekendReg()
    {
        if (!is_null($this->data->bef)){
            return $this->data->bef;
        } else if (!is_null($this->data->aft)){
            return $this->data->aft;
        } else {
            return $this->data->dur;
        }
    }
}
